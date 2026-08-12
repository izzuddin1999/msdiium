<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite rebuilds a table when adding a constrained column. A view that
        // references that table prevents the temporary table rename, so remove
        // and restore the compatibility view around the users alteration.
        $usesSqlite = DB::connection()->getDriverName() === 'sqlite';
        if ($usesSqlite) {
            DB::statement('PRAGMA legacy_alter_table = ON');
            DB::statement('DROP VIEW IF EXISTS user_cas');
        }

        Schema::create('organizations', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 180);
            $table->string('organization_type', 30);
            $table->foreignId('parent_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('organizations')->insert([
            [
                'code' => 'MSD',
                'name' => 'Management Services Division',
                'organization_type' => 'division',
                'parent_id' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'KCDIOM',
                'name' => 'Kulliyyah, Centre, Division, Institute and Office Management',
                'organization_type' => 'group',
                'parent_id' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        foreach (['users', 'policy_documents', 'topic_categories', 'lookup_values', 'form_templates', 'organization_profiles'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->foreignId('organization_id')->nullable()->index()
                    ->constrained('organizations')->nullOnDelete();
            });
        }

        $msdId = DB::table('organizations')->where('code', 'MSD')->value('id');
        $kcdiomId = DB::table('organizations')->where('code', 'KCDIOM')->value('id');

        DB::table('users')->where('unit', 'kcdiom')->update(['organization_id' => $kcdiomId]);
        DB::table('users')->whereIn('unit', ['all', 'msd'])->update(['organization_id' => $msdId]);

        foreach (['policy_documents', 'topic_categories', 'lookup_values', 'form_templates'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            DB::table($tableName)->where('owner_unit', 'kcdiom')->update(['organization_id' => $kcdiomId]);
            DB::table($tableName)->where('owner_unit', 'msd')->update(['organization_id' => $msdId]);
        }

        if (Schema::hasTable('organization_profiles')) {
            DB::table('organization_profiles')->whereRaw('LOWER(code) = ?', ['msd'])->update(['organization_id' => $msdId]);
            DB::table('organization_profiles')->whereRaw('LOWER(code) = ?', ['kcdiom'])->update(['organization_id' => $kcdiomId]);
        }

        if ($usesSqlite) {
            DB::statement('CREATE VIEW user_cas AS SELECT id AS user_id, staff_id, name FROM users');
        }
    }

    public function down(): void
    {
        foreach (['organization_profiles', 'form_templates', 'lookup_values', 'topic_categories', 'policy_documents', 'users'] as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'organization_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropConstrainedForeignId('organization_id');
            });
        }

        Schema::dropIfExists('organizations');
    }
};
