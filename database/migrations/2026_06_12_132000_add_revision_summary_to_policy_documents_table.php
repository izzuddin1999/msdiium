<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('policy_documents', function (Blueprint $table): void {
            $table->text('revision_summary')->nullable()->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('policy_documents', function (Blueprint $table): void {
            $table->dropColumn('revision_summary');
        });
    }
};