<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (['topic_categories', 'topic_subtopics', 'topic_details'] as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'sort_order')) {
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->unsignedInteger('sort_order')->default(0)->index());
            }
        }
    }

    public function down(): void
    {
        foreach (['topic_categories', 'topic_subtopics', 'topic_details'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'sort_order')) {
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropColumn('sort_order'));
            }
        }
    }
};
