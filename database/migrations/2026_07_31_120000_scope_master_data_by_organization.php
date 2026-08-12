<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('topic_categories', function (Blueprint $table): void {
            $table->string('owner_unit', 20)->default('msd')->after('slug')->index();
            $table->dropUnique(['name']);
            $table->dropUnique(['slug']);
            $table->unique(['owner_unit', 'name']);
            $table->unique(['owner_unit', 'slug']);
        });

        Schema::table('lookup_values', function (Blueprint $table): void {
            $table->string('owner_unit', 20)->default('msd')->after('type')->index();
            $table->dropUnique(['type', 'code']);
            $table->unique(['owner_unit', 'type', 'code']);
        });
    }

    public function down(): void
    {
        Schema::table('lookup_values', function (Blueprint $table): void {
            $table->dropUnique(['owner_unit', 'type', 'code']);
            $table->unique(['type', 'code']);
            $table->dropColumn('owner_unit');
        });

        Schema::table('topic_categories', function (Blueprint $table): void {
            $table->dropUnique(['owner_unit', 'name']);
            $table->dropUnique(['owner_unit', 'slug']);
            $table->unique('name');
            $table->unique('slug');
            $table->dropColumn('owner_unit');
        });
    }
};
