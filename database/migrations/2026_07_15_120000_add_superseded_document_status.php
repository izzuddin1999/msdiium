<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // A fresh installation receives all lifecycle values from DatabaseSeeder.
        // Only patch an existing configured LOV set during an upgrade.
        if (! DB::table('lookup_values')->where('type', 'DOCUMENT_STATUS')->exists()) {
            return;
        }

        DB::table('lookup_values')->updateOrInsert(
            ['type' => 'DOCUMENT_STATUS', 'code' => 'superseded'],
            ['description' => 'Superseded', 'sort_order' => 4, 'is_active' => true, 'updated_at' => now(), 'created_at' => now()]
        );

        DB::table('lookup_values')
            ->where('type', 'DOCUMENT_STATUS')
            ->where('code', 'archived')
            ->where('sort_order', 4)
            ->update(['sort_order' => 5, 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('lookup_values')
            ->where('type', 'DOCUMENT_STATUS')
            ->where('code', 'superseded')
            ->whereNotExists(fn ($query) => $query->selectRaw('1')->from('policy_documents')->whereColumn('policy_documents.status', 'lookup_values.code'))
            ->delete();
    }
};
