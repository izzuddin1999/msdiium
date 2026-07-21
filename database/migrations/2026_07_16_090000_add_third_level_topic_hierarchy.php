<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS document');

        if (Schema::hasTable('__temp__policy_documents') && ! Schema::hasTable('policy_documents')) {
            DB::statement('ALTER TABLE "__temp__policy_documents" RENAME TO "policy_documents"');
        }

        if (! Schema::hasTable('topic_details')) {
            Schema::create('topic_details', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('main_topic_id')->constrained('topic_subtopics')->cascadeOnDelete();
                $table->string('name');
                $table->string('slug')->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['main_topic_id', 'name']);
            });
        }

        if (! Schema::hasColumn('policy_documents', 'topic_detail_id')) {
            Schema::table('policy_documents', function (Blueprint $table): void {
                $table->foreignId('topic_detail_id')->nullable()->after('subtopic_id')->constrained('topic_details')->nullOnDelete();
            });
        }

        $this->createDocumentView();
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS document');
        Schema::table('policy_documents', fn (Blueprint $table) => $table->dropConstrainedForeignId('topic_detail_id'));
        Schema::dropIfExists('topic_details');
        $this->createDocumentView();
    }

    private function createDocumentView(): void
    {
        DB::statement(<<<'SQL'
            CREATE VIEW document AS
            SELECT d.id AS document_id, d.title AS document_title, d.document_type,
                   d.reference_number AS reference_no, d.remarks, d.public_flag,
                   d.created_by, d.created_at AS created_date,
                   d.effective_date AS start_date, d.expiry_date AS end_date,
                   mt.id AS main_topic_id, d.subtopic_id AS sub_topic_id, d.created_by AS user_id
            FROM policy_documents d
            LEFT JOIN topic_categories mt ON mt.slug = d.topic_category
        SQL);
    }
};
