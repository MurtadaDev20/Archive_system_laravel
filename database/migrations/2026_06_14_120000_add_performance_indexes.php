<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfMissing('files', 'files_status_id_index', fn (Blueprint $table) => $table->index('status_id'));
        $this->addIndexIfMissing('files', 'files_dep_id_index', fn (Blueprint $table) => $table->index('dep_id'));
        $this->addIndexIfMissing('files', 'files_folder_id_index', fn (Blueprint $table) => $table->index('folder_id'));
        $this->addIndexIfMissing('files', 'files_user_id_index', fn (Blueprint $table) => $table->index('user_id'));
        $this->addIndexIfMissing('files', 'files_created_at_index', fn (Blueprint $table) => $table->index('created_at'));

        $this->addIndexIfMissing('document_transfers', 'document_transfers_file_id_status_index', fn (Blueprint $table) => $table->index(['file_id', 'status']));
        $this->addIndexIfMissing('document_transfers', 'document_transfers_to_department_id_index', fn (Blueprint $table) => $table->index('to_department_id'));

        $this->addIndexIfMissing('folders', 'folders_user_id_dep_id_index', fn (Blueprint $table) => $table->index(['user_id', 'dep_id']));
    }

    public function down(): void
    {
        $this->dropIndexIfExists('files', 'files_status_id_index');
        $this->dropIndexIfExists('files', 'files_dep_id_index');
        $this->dropIndexIfExists('files', 'files_folder_id_index');
        $this->dropIndexIfExists('files', 'files_user_id_index');
        $this->dropIndexIfExists('files', 'files_created_at_index');

        $this->dropIndexIfExists('document_transfers', 'document_transfers_file_id_status_index');
        $this->dropIndexIfExists('document_transfers', 'document_transfers_to_department_id_index');

        $this->dropIndexIfExists('folders', 'folders_user_id_dep_id_index');
    }

    private function tableIndexes(string $table): array
    {
        return collect(DB::select("SHOW INDEX FROM `{$table}`"))
            ->pluck('Key_name')
            ->unique()
            ->all();
    }

    private function addIndexIfMissing(string $table, string $indexName, callable $callback): void
    {
        if (in_array($indexName, $this->tableIndexes($table), true)) {
            return;
        }

        Schema::table($table, $callback);
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (! in_array($indexName, $this->tableIndexes($table), true)) {
            return;
        }

        Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropIndex($indexName));
    }
};
