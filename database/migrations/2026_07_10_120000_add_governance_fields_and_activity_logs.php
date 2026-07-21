<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('policy_documents', function (Blueprint $table): void {
            $table->string('reference_number', 100)->nullable()->unique()->after('title');
            $table->date('effective_date')->nullable()->after('revision_summary');
            $table->date('expiry_date')->nullable()->after('effective_date');
            $table->text('remarks')->nullable()->after('expiry_date');
        });

        Schema::create('document_activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('policy_document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 50);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['policy_document_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_activity_logs');

        Schema::table('policy_documents', function (Blueprint $table): void {
            $table->dropUnique(['reference_number']);
            $table->dropColumn(['reference_number', 'effective_date', 'expiry_date', 'remarks']);
        });
    }
};
