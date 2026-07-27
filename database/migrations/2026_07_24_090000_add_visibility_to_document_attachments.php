<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_attachments', function (Blueprint $table): void {
            $table->boolean('is_public')->default(false)->after('security_status');
            $table->index(['policy_document_id', 'is_public']);
        });
    }

    public function down(): void
    {
        Schema::table('document_attachments', function (Blueprint $table): void {
            $table->dropIndex(['policy_document_id', 'is_public']);
            $table->dropColumn('is_public');
        });
    }
};
