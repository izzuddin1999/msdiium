<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('email', 'msd.admin@iium.edu.my')->update(['role' => 'system_admin', 'unit' => 'all']);
    }

    public function down(): void
    {
        DB::table('users')->where('email', 'msd.admin@iium.edu.my')->update(['role' => 'policy_manager', 'unit' => 'msd']);
    }
};
