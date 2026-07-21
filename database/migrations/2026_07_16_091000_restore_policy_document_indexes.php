<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $existing = DB::connection()->getDriverName() === 'sqlite'
            ? collect(DB::select("PRAGMA index_list('policy_documents')"))->pluck('name')
            : collect();

        Schema::table('policy_documents', function (Blueprint $table) use ($existing): void {
            if (! $existing->contains('policy_documents_document_type_status_index')) {
                $table->index(['document_type', 'status']);
            }
            if (! $existing->contains('policy_documents_title_version_number_index')) {
                $table->index(['title', 'version_number']);
            }
            if (! $existing->contains('policy_documents_reference_number_unique')) {
                $table->unique('reference_number');
            }
        });
    }

    public function down(): void
    {
        // These are baseline integrity indexes and intentionally remain in place.
    }
};
