<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('policy_documents', function (Blueprint $table): void {
            if (! Schema::hasIndex('policy_documents', 'policy_documents_document_type_status_index')) {
                $table->index(['document_type', 'status']);
            }
            if (! Schema::hasIndex('policy_documents', 'policy_documents_title_version_number_index')) {
                $table->index(['title', 'version_number']);
            }
            if (! Schema::hasIndex('policy_documents', 'policy_documents_reference_number_unique')) {
                $table->unique('reference_number');
            }
        });
    }

    public function down(): void
    {
        // These are baseline integrity indexes and intentionally remain in place.
    }
};
