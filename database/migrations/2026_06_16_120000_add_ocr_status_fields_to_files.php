<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('files', function (Blueprint $table) {
            if (! Schema::hasColumn('files', 'ocr_status')) {
                $table->string('ocr_status', 32)->default('pending')->after('ocr_text');
                $table->timestamp('ocr_processed_at')->nullable()->after('ocr_status');
                $table->text('ocr_error')->nullable()->after('ocr_processed_at');
                $table->string('ocr_languages', 64)->nullable()->after('ocr_error');
                $table->unsignedSmallInteger('ocr_page_count')->nullable()->after('ocr_languages');
                $table->index('ocr_status');
            }
        });

        if (Schema::hasColumn('files', 'ocr_status')) {
            DB::table('files')
                ->whereNull('ocr_status')
                ->update(['ocr_status' => 'pending']);
        }

        $this->addFulltextIndexIfSupported();
    }

    public function down(): void
    {
        $this->dropFulltextIndexIfExists();

        Schema::table('files', function (Blueprint $table) {
            if (Schema::hasColumn('files', 'ocr_status')) {
                $table->dropIndex(['ocr_status']);
                $table->dropColumn([
                    'ocr_status',
                    'ocr_processed_at',
                    'ocr_error',
                    'ocr_languages',
                    'ocr_page_count',
                ]);
            }
        });
    }

    private function addFulltextIndexIfSupported(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $exists = collect(DB::select("SHOW INDEX FROM files WHERE Key_name = 'files_ocr_fulltext'"))->isNotEmpty();

        if ($exists) {
            return;
        }

        try {
            DB::statement('ALTER TABLE files ADD FULLTEXT INDEX files_ocr_fulltext (file_name, description, ocr_text)');
        } catch (\Throwable) {
            // InnoDB fulltext may fail on very old MySQL — LIKE search still works
        }
    }

    private function dropFulltextIndexIfExists(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        try {
            DB::statement('ALTER TABLE files DROP INDEX files_ocr_fulltext');
        } catch (\Throwable) {
            // ignore
        }
    }
};
