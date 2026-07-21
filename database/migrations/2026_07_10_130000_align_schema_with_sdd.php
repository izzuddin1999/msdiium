<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('staff_id', 20)->nullable()->unique()->after('id');
            $table->string('cas_username')->nullable()->unique()->after('staff_id');
            $table->timestamp('last_cas_sync_at')->nullable()->after('is_active');
        });

        Schema::table('policy_documents', function (Blueprint $table): void {
            $table->boolean('public_flag')->default(false)->after('access_scope');
            $table->string('owner_report', 100)->nullable()->after('owner_unit');
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
        });

        Schema::create('lookup_values', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 50);
            $table->string('code', 50);
            $table->string('description');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['type', 'code']);
        });

        Schema::create('document_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('policy_document_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('status', 30);
            $table->text('revision_summary')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['policy_document_id', 'version_number']);
        });

        Schema::create('document_attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('policy_document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_history_id')->nullable()->constrained()->nullOnDelete();
            $table->string('file_name');
            $table->string('file_path', 500);
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('file_type', 50)->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['policy_document_id', 'created_at']);
        });

        foreach (DB::table('policy_documents')->orderBy('id')->get() as $document) {
            $rootId = $document->parent_document_id ?: $document->id;
            $historyId = DB::table('document_histories')->insertGetId([
                'policy_document_id' => $rootId,
                'version_number' => $document->version_number,
                'status' => $document->status,
                'revision_summary' => $document->revision_summary,
                'created_by' => $document->created_by,
                'published_at' => $document->published_at,
                'created_at' => $document->created_at,
                'updated_at' => $document->updated_at,
            ]);

            if ($document->file_path) {
                DB::table('document_attachments')->insert([
                    'policy_document_id' => $rootId,
                    'document_history_id' => $historyId,
                    'file_name' => $document->file_original_name ?: basename($document->file_path),
                    'file_path' => $document->file_path,
                    'uploaded_by' => $document->created_by,
                    'created_at' => $document->created_at,
                    'updated_at' => $document->updated_at,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('document_attachments');
        Schema::dropIfExists('document_histories');
        Schema::dropIfExists('lookup_values');

        Schema::table('policy_documents', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('updated_by');
            $table->dropColumn(['public_flag', 'owner_report']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['staff_id']);
            $table->dropUnique(['cas_username']);
            $table->dropColumn(['staff_id', 'cas_username', 'last_cas_sync_at']);
        });
    }
};
