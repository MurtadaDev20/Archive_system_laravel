<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->index(['department_id', 'is_active']);
            });
        }

        if (! Schema::hasTable('tags')) {
            Schema::create('tags', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('color', 20)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('document_types')) {
            Schema::create('document_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('statuses') && ! Schema::hasColumn('statuses', 'slug')) {
            Schema::table('statuses', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('name');
                $table->string('label_ar')->nullable()->after('slug');
                $table->unsignedTinyInteger('sort_order')->default(0)->after('label_ar');
                $table->string('color', 20)->nullable()->after('sort_order');
            });
        }

        if (Schema::hasTable('files') && ! Schema::hasColumn('files', 'document_number')) {
            Schema::table('files', function (Blueprint $table) {
                $table->string('document_number')->nullable()->unique()->after('code');
                $table->text('description')->nullable()->after('file_name');
                $table->foreignId('category_id')->nullable()->after('dep_id')->constrained('categories')->nullOnDelete();
                $table->foreignId('document_type_id')->nullable()->after('category_id')->constrained('document_types')->nullOnDelete();
                $table->foreignId('owner_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
                $table->foreignId('approved_by')->nullable()->after('owner_id')->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable()->after('approved_by');
                $table->date('expiry_date')->nullable()->after('approved_at');
                $table->date('archive_date')->nullable()->after('expiry_date');
                $table->string('qr_code_path')->nullable()->after('archive_date');
                $table->text('notes')->nullable()->after('qr_code_path');
                $table->longText('ocr_text')->nullable()->after('notes');
                $table->unsignedInteger('current_version')->default(1)->after('ocr_text');
                $table->timestamp('deleted_at')->nullable()->after('updated_at');
                $table->index('document_number');
                $table->index('expiry_date');
                $table->index('archive_date');
                $table->index(['status_id', 'dep_id']);
                $table->index(['user_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('document_tag')) {
            Schema::create('document_tag', function (Blueprint $table) {
                $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
                $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
                $table->primary(['file_id', 'tag_id']);
            });
        }

        if (! Schema::hasTable('document_versions')) {
            Schema::create('document_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
                $table->unsignedInteger('version_number');
                $table->string('storage_path');
                $table->string('original_name');
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('size')->default(0);
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('change_notes')->nullable();
                $table->timestamps();
                $table->unique(['file_id', 'version_number']);
            });
        }

        if (! Schema::hasTable('document_transfers')) {
            Schema::create('document_transfers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
                $table->foreignId('from_department_id')->constrained('departments');
                $table->foreignId('to_department_id')->constrained('departments');
                $table->foreignId('from_user_id')->constrained('users');
                $table->foreignId('to_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('status', 30)->default('sent');
                $table->text('comment')->nullable();
                $table->text('response_comment')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('received_at')->nullable();
                $table->timestamp('responded_at')->nullable();
                $table->timestamps();
                $table->index(['file_id', 'status']);
            });
        }

        if (! Schema::hasTable('document_workflow_logs')) {
            Schema::create('document_workflow_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
                $table->foreignId('from_status_id')->nullable()->constrained('statuses')->nullOnDelete();
                $table->foreignId('to_status_id')->constrained('statuses');
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('comment')->nullable();
                $table->timestamps();
                $table->index(['file_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('document_comments')) {
            Schema::create('document_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->text('body');
                $table->timestamps();
            });
        }

        if (Schema::hasColumn('statuses', 'slug') && DB::table('statuses')->whereNull('slug')->exists()) {
            $this->migrateStatuses();
        }

        $this->migrateExistingFiles();
    }

    public function down(): void
    {
        Schema::dropIfExists('document_comments');
        Schema::dropIfExists('document_workflow_logs');
        Schema::dropIfExists('document_transfers');
        Schema::dropIfExists('document_versions');
        Schema::dropIfExists('document_tag');
        if (Schema::hasColumn('files', 'document_number')) {
            Schema::table('files', function (Blueprint $table) {
                $table->dropSoftDeletes();
                $table->dropForeign(['category_id']);
                $table->dropForeign(['document_type_id']);
                $table->dropForeign(['owner_id']);
                $table->dropForeign(['approved_by']);
                $table->dropColumn([
                    'document_number', 'description', 'category_id', 'document_type_id',
                    'owner_id', 'approved_by', 'approved_at', 'expiry_date', 'archive_date',
                    'qr_code_path', 'notes', 'ocr_text', 'current_version',
                ]);
            });
        }
        if (Schema::hasColumn('statuses', 'slug')) {
            Schema::table('statuses', function (Blueprint $table) {
                $table->dropColumn(['slug', 'label_ar', 'sort_order', 'color']);
            });
        }
        Schema::dropIfExists('document_types');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('categories');
    }

    private function migrateStatuses(): void
    {
        Schema::disableForeignKeyConstraints();

        $map = [1 => 5, 2 => 4, 3 => 6];
        foreach ($map as $old => $new) {
            DB::table('files')->where('status_id', $old)->update(['status_id' => $new]);
        }

        DB::table('statuses')->truncate();

        $statuses = [
            ['name' => 'Draft', 'slug' => 'draft', 'label_ar' => 'مسودة', 'sort_order' => 1, 'color' => 'secondary'],
            ['name' => 'Pending Review', 'slug' => 'pending_review', 'label_ar' => 'بانتظار المراجعة', 'sort_order' => 2, 'color' => 'info'],
            ['name' => 'Under Review', 'slug' => 'under_review', 'label_ar' => 'قيد المراجعة', 'sort_order' => 3, 'color' => 'primary'],
            ['name' => 'Pending Approval', 'slug' => 'pending_approval', 'label_ar' => 'بانتظار الاعتماد', 'sort_order' => 4, 'color' => 'warning'],
            ['name' => 'Approved', 'slug' => 'approved', 'label_ar' => 'معتمد', 'sort_order' => 5, 'color' => 'success'],
            ['name' => 'Rejected', 'slug' => 'rejected', 'label_ar' => 'مرفوض', 'sort_order' => 6, 'color' => 'danger'],
            ['name' => 'Archived', 'slug' => 'archived', 'label_ar' => 'مؤرشف', 'sort_order' => 7, 'color' => 'dark'],
            ['name' => 'Expired', 'slug' => 'expired', 'label_ar' => 'منتهي الصلاحية', 'sort_order' => 8, 'color' => 'danger'],
        ];

        foreach ($statuses as $status) {
            DB::table('statuses')->insert(array_merge($status, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        Schema::enableForeignKeyConstraints();
    }

    private function migrateExistingFiles(): void
    {
        if (! Schema::hasColumn('files', 'document_number')) {
            return;
        }

        foreach (DB::table('files')->whereNull('document_number')->get() as $file) {
            DB::table('files')->where('id', $file->id)->update([
                'document_number' => $file->code ?: 'DOC-'.str_pad((string) $file->id, 6, '0', STR_PAD_LEFT),
                'owner_id' => $file->user_id,
            ]);
        }
    }
};
