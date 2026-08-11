<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $statuses = [
            'draft' => ['Draft', 1],
            'published' => ['Active', 2],
            'superseded' => ['Superceded', 3],
        ];

        foreach ($statuses as $code => [$description, $order]) {
            DB::table('lookup_values')->updateOrInsert(
                ['type' => 'DOCUMENT_STATUS', 'code' => $code],
                ['description' => $description, 'sort_order' => $order, 'is_active' => true, 'updated_at' => now(), 'created_at' => now()],
            );
        }

        DB::table('lookup_values')->where('type', 'DOCUMENT_STATUS')
            ->whereNotIn('code', array_keys($statuses))->update(['is_active' => false, 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('lookup_values')->where('type', 'DOCUMENT_STATUS')->where('code', 'published')->update(['description' => 'Published']);
        DB::table('lookup_values')->where('type', 'DOCUMENT_STATUS')->where('code', 'superseded')->update(['description' => 'Superseded']);
        DB::table('lookup_values')->where('type', 'DOCUMENT_STATUS')->whereIn('code', ['inactive', 'archived'])->update(['is_active' => true]);
    }
};
