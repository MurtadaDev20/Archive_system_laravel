<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $pendingApprovalId = DB::table('statuses')->where('slug', 'pending_approval')->value('id');
        $legacyIds = DB::table('statuses')
            ->whereIn('slug', ['pending_review', 'under_review'])
            ->pluck('id');

        if ($pendingApprovalId && $legacyIds->isNotEmpty()) {
            DB::table('files')
                ->whereIn('status_id', $legacyIds)
                ->update(['status_id' => $pendingApprovalId]);
        }

        DB::table('statuses')->where('slug', 'pending_review')->update([
            'label_ar' => 'بانتظار الاعتماد (قديم)',
            'sort_order' => 90,
        ]);

        DB::table('statuses')->where('slug', 'pending_approval')->update([
            'label_ar' => 'بانتظار الاعتماد',
            'sort_order' => 2,
        ]);

        DB::table('statuses')->where('slug', 'under_review')->update([
            'label_ar' => 'قيد المراجعة (قديم)',
            'sort_order' => 91,
        ]);

        DB::table('statuses')->where('slug', 'draft')->update(['sort_order' => 1]);
        DB::table('statuses')->where('slug', 'approved')->update(['sort_order' => 3]);
        DB::table('statuses')->where('slug', 'archived')->update(['sort_order' => 4]);
    }

    public function down(): void
    {
        // لا تراجع تلقائي — الحالات القديمة مُدمَجة في pending_approval
    }
};
