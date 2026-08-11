<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_attachments', function (Blueprint $table): void {
            $table->string('checksum_sha256', 64)->nullable()->after('file_type');
            $table->string('security_status', 30)->default('validated')->after('checksum_sha256');
            $table->timestamp('integrity_verified_at')->nullable()->after('security_status');
        });
    }

    public function down(): void
    {
        Schema::table('document_attachments', function (Blueprint $table): void {
            $table->dropColumn(['checksum_sha256', 'security_status', 'integrity_verified_at']);
        });
    }
};
