<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expiry_reminder_dispatches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('policy_document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('expiry_date');
            $table->unsignedInteger('reminder_days');
            $table->timestamp('dispatched_at');
            $table->timestamps();
            $table->unique(['policy_document_id', 'user_id', 'expiry_date', 'reminder_days'], 'expiry_reminder_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expiry_reminder_dispatches');
    }
};
