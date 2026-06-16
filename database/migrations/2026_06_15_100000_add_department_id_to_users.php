<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->after('manager_id');
                $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
                $table->index('department_id');
            }
        });

        $this->backfillDepartmentIds();
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'department_id')) {
                $table->dropForeign(['department_id']);
                $table->dropColumn('department_id');
            }
        });
    }

    private function backfillDepartmentIds(): void
    {
        $managed = DB::table('departments')
            ->whereNotNull('manager_id')
            ->pluck('id', 'manager_id');

        foreach ($managed as $managerUserId => $departmentId) {
            DB::table('users')
                ->where('id', $managerUserId)
                ->whereNull('department_id')
                ->update(['department_id' => $departmentId]);
        }

        $employees = DB::table('users')
            ->whereNotNull('manager_id')
            ->whereNull('department_id')
            ->get(['id', 'manager_id']);

        foreach ($employees as $employee) {
            $departmentId = $managed[$employee->manager_id] ?? null;

            if (! $departmentId) {
                $departmentId = DB::table('folders')
                    ->where('user_id', $employee->manager_id)
                    ->value('dep_id');
            }

            if ($departmentId) {
                DB::table('users')
                    ->where('id', $employee->id)
                    ->update(['department_id' => $departmentId]);
            }
        }

        $fromFolders = DB::table('folders')
            ->select('user_id', DB::raw('MIN(dep_id) as dep_id'))
            ->groupBy('user_id')
            ->get();

        foreach ($fromFolders as $row) {
            if (! $row->dep_id) {
                continue;
            }

            DB::table('users')
                ->where('id', $row->user_id)
                ->whereNull('department_id')
                ->update(['department_id' => $row->dep_id]);
        }
    }
};
