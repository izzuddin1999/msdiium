<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $legacySlugs = ['compliance', 'general'];

        DB::table('policy_documents')
            ->whereIn('topic_category', $legacySlugs)
            ->update([
                'topic_category' => null,
                'subtopic_id' => null,
            ]);

        DB::table('topic_categories')->whereIn('slug', $legacySlugs)->delete();
    }

    public function down(): void
    {
        foreach (['Compliance' => 'compliance', 'General' => 'general'] as $name => $slug) {
            DB::table('topic_categories')->updateOrInsert(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
};
