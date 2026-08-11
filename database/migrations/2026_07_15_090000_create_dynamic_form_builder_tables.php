<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('document_type', 50)->nullable();
            $table->string('owner_unit', 30)->default('kcdiom');
            $table->unsignedTinyInteger('columns')->default(3);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('form_fields', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('form_template_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('name', 80);
            $table->string('type', 30);
            $table->string('section')->default('Additional information');
            $table->unsignedTinyInteger('width')->default(1);
            $table->boolean('is_required')->default(false);
            $table->string('placeholder')->nullable();
            $table->text('help_text')->nullable();
            $table->text('default_value')->nullable();
            $table->json('options')->nullable();
            $table->string('data_source')->nullable();
            $table->json('validation')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['form_template_id', 'name']);
        });

        Schema::create('document_form_responses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('policy_document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('form_template_id')->constrained()->restrictOnDelete();
            $table->json('values');
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['policy_document_id', 'form_template_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_form_responses');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('form_templates');
    }
};
