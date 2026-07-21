<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('topic_subtopics', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('topic_category_id')->constrained('topic_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['topic_category_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topic_subtopics');
    }
};
