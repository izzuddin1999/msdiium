<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('policy_documents', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('document_type');
            $table->text('content')->nullable();
            $table->string('access_scope')->default('all');
            $table->string('owner_unit')->default('msd');
            $table->string('status')->default('draft');
            $table->boolean('is_circular')->default(false);
            $table->unsignedInteger('version_number')->default(1);
            $table->foreignId('parent_document_id')->nullable()->constrained('policy_documents')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_path')->nullable();
            $table->string('file_original_name')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['title', 'version_number']);
            $table->index(['document_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_documents');
    }
};
