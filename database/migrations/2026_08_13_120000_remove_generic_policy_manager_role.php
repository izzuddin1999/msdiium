<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'policy_manager')
            ->where('unit', 'kcdiom')
            ->update(['role' => 'kcdiom_liaison']);

        DB::table('users')
            ->where('role', 'policy_manager')
            ->update(['role' => 'msd_admin', 'unit' => 'msd']);
    }

    public function down(): void
    {
        DB::table('users')
            ->whereIn('role', ['msd_admin', 'kcdiom_liaison'])
            ->update(['role' => 'policy_manager']);
    }
};
