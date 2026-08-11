<?php

namespace Tests\Feature;

use App\Models\LookupValue;
use App\Models\PolicyDocument;
use App\Models\TopicCategory;
use App\Models\TopicSubtopic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ErdDatabaseContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_erd_entities_expose_the_required_database_columns(): void
    {
        $contracts = [
            'main_topic' => ['main_topic_id', 'topic_code', 'topic_name', 'description', 'category', 'status'],
            'sub_topic' => ['sub_topic_id', 'sub_topic_name', 'description', 'status', 'main_topic_id'],
            'user_cas' => ['user_id', 'staff_id', 'name'],
            'document' => ['document_id', 'document_title', 'document_type', 'reference_no', 'remarks', 'public_flag', 'created_by', 'created_date', 'start_date', 'end_date', 'main_topic_id', 'sub_topic_id', 'user_id'],
            'notification' => ['notification_id', 'document_id', 'recipient_user_id', 'message', 'notification_type', 'created_date', 'is_read'],
            'lov_main' => ['lov_id', 'lv_type', 'lov_code', 'lov_description', 'sort_order', 'is_active'],
            'document_attachment' => ['attachment_id', 'file_name', 'file_path', 'file_size', 'file_type', 'uploaded_by', 'upload_date', 'history_id'],
            'document_history' => ['history_id', 'document_id', 'version_no', 'status', 'attachment_id'],
            'document_log' => ['log_id', 'document_id', 'user_id', 'action_type', 'action_by', 'action_date', 'old_value', 'new_value'],
        ];

        foreach ($contracts as $entity => $columns) {
            $this->assertNotNull(DB::table($entity)->select($columns)->limit(1)->get());
        }
    }

    public function test_erd_contract_is_live_over_application_records(): void
    {
        $user = User::factory()->create(['staff_id' => 'IIUM-1001']);
        $main = TopicCategory::create(['name' => 'Governance', 'slug' => 'governance', 'is_active' => true]);
        $sub = TopicSubtopic::create(['topic_category_id' => $main->id, 'name' => 'Policy Control', 'slug' => 'policy-control', 'is_active' => true]);
        $document = PolicyDocument::create([
            'title' => 'ERD Contract Policy', 'document_type' => 'policy', 'reference_number' => 'ERD/001',
            'remarks' => 'Mapped record', 'public_flag' => true, 'created_by' => $user->id,
            'topic_category' => $main->slug, 'subtopic_id' => $sub->id, 'status' => 'draft',
            'version_number' => 1,
        ]);
        $lookup = LookupValue::create(['type' => 'DOCUMENT_TYPE', 'code' => 'procedure', 'description' => 'Procedure', 'sort_order' => 8, 'is_active' => true]);

        $this->assertSame('Governance', DB::table('main_topic')->where('main_topic_id', $main->id)->value('topic_name'));
        $this->assertSame($main->id, DB::table('sub_topic')->where('sub_topic_id', $sub->id)->value('main_topic_id'));
        $this->assertSame('IIUM-1001', DB::table('user_cas')->where('user_id', $user->id)->value('staff_id'));
        $this->assertSame($main->id, DB::table('document')->where('document_id', $document->id)->value('main_topic_id'));
        $this->assertSame('procedure', DB::table('lov_main')->where('lov_id', $lookup->id)->value('lov_code'));
    }
}
