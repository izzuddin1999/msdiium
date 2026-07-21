<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const VIEWS = [
        'main_topic', 'sub_topic', 'user_cas', 'lov_main', 'document',
        'notification', 'document_attachment', 'document_history', 'document_log',
    ];

    public function up(): void
    {
        $this->dropViews();

        DB::statement(<<<'SQL'
            CREATE VIEW main_topic AS
            SELECT id AS main_topic_id,
                   slug AS topic_code,
                   name AS topic_name,
                   NULL AS description,
                   NULL AS category,
                   CASE WHEN is_active = 1 THEN 'ACTIVE' ELSE 'INACTIVE' END AS status
            FROM topic_categories
        SQL);

        DB::statement(<<<'SQL'
            CREATE VIEW sub_topic AS
            SELECT id AS sub_topic_id,
                   name AS sub_topic_name,
                   NULL AS description,
                   CASE WHEN is_active = 1 THEN 'ACTIVE' ELSE 'INACTIVE' END AS status,
                   topic_category_id AS main_topic_id
            FROM topic_subtopics
        SQL);

        DB::statement(<<<'SQL'
            CREATE VIEW user_cas AS
            SELECT id AS user_id, staff_id, name
            FROM users
        SQL);

        DB::statement(<<<'SQL'
            CREATE VIEW lov_main AS
            SELECT id AS lov_id,
                   type AS lv_type,
                   code AS lov_code,
                   description AS lov_description,
                   sort_order,
                   is_active
            FROM lookup_values
        SQL);

        DB::statement(<<<'SQL'
            CREATE VIEW document AS
            SELECT d.id AS document_id,
                   d.title AS document_title,
                   d.document_type,
                   d.reference_number AS reference_no,
                   d.remarks,
                   d.public_flag,
                   d.created_by,
                   d.created_at AS created_date,
                   d.effective_date AS start_date,
                   d.expiry_date AS end_date,
                   mt.id AS main_topic_id,
                   d.subtopic_id AS sub_topic_id,
                   d.created_by AS user_id
            FROM policy_documents d
            LEFT JOIN topic_categories mt ON mt.slug = d.topic_category
        SQL);

        $driver = DB::connection()->getDriverName();
        $documentId = $driver === 'sqlite'
            ? "json_extract(data, '$.document_id')"
            : "JSON_UNQUOTE(JSON_EXTRACT(data, '$.document_id'))";
        $message = $driver === 'sqlite'
            ? "COALESCE(json_extract(data, '$.message'), data)"
            : "COALESCE(JSON_UNQUOTE(JSON_EXTRACT(data, '$.message')), data)";

        DB::statement("CREATE VIEW notification AS
            SELECT id AS notification_id,
                   {$documentId} AS document_id,
                   notifiable_id AS recipient_user_id,
                   {$message} AS message,
                   type AS notification_type,
                   created_at AS created_date,
                   CASE WHEN read_at IS NULL THEN 0 ELSE 1 END AS is_read
            FROM notifications");

        DB::statement(<<<'SQL'
            CREATE VIEW document_attachment AS
            SELECT id AS attachment_id,
                   file_name,
                   file_path,
                   file_size,
                   file_type,
                   uploaded_by,
                   created_at AS upload_date,
                   document_history_id AS history_id
            FROM document_attachments
        SQL);

        DB::statement(<<<'SQL'
            CREATE VIEW document_history AS
            SELECT h.id AS history_id,
                   h.policy_document_id AS document_id,
                   h.version_number AS version_no,
                   h.status,
                   (SELECT a.id FROM document_attachments a
                    WHERE a.document_history_id = h.id ORDER BY a.id LIMIT 1) AS attachment_id
            FROM document_histories h
        SQL);

        DB::statement(<<<'SQL'
            CREATE VIEW document_log AS
            SELECT id AS log_id,
                   policy_document_id AS document_id,
                   user_id,
                   action AS action_type,
                   user_id AS action_by,
                   created_at AS action_date,
                   old_values AS old_value,
                   new_values AS new_value
            FROM document_activity_logs
        SQL);
    }

    public function down(): void
    {
        $this->dropViews();
    }

    private function dropViews(): void
    {
        foreach (array_reverse(self::VIEWS) as $view) {
            DB::statement("DROP VIEW IF EXISTS {$view}");
        }
    }
};
