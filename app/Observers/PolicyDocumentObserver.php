<?php

namespace App\Observers;

use App\Models\DocumentActivityLog;
use App\Models\DocumentHistory;
use App\Models\PolicyDocument;
use Illuminate\Support\Arr;

class PolicyDocumentObserver
{
    private const TRACKED_FIELDS = [
        'title', 'reference_number', 'document_type', 'topic_category', 'subtopic_id',
        'content', 'revision_summary', 'effective_date', 'expiry_date', 'remarks',
        'access_scope', 'public_flag', 'owner_unit', 'owner_report', 'status', 'is_circular', 'version_number',
        'parent_document_id', 'created_by', 'updated_by', 'file_path', 'file_original_name',
        'published_at', 'published_by',
    ];

    public function created(PolicyDocument $document): void
    {
        DocumentHistory::updateOrCreate(
            [
                'policy_document_id' => $document->parent_document_id ?: $document->id,
                'version_number' => $document->version_number,
            ],
            [
                'status' => $document->status,
                'revision_summary' => $document->revision_summary,
                'created_by' => $document->created_by,
                'published_at' => $document->published_at,
            ]
        );

        $this->record($document, 'created', null, Arr::only($document->getAttributes(), self::TRACKED_FIELDS));
    }

    public function updated(PolicyDocument $document): void
    {
        $changes = Arr::only($document->getChanges(), self::TRACKED_FIELDS);

        if ($changes === []) {
            return;
        }

        DocumentHistory::updateOrCreate(
            [
                'policy_document_id' => $document->parent_document_id ?: $document->id,
                'version_number' => $document->version_number,
            ],
            [
                'status' => $document->status,
                'revision_summary' => $document->revision_summary,
                'created_by' => $document->created_by,
                'published_at' => $document->published_at,
            ]
        );

        $old = [];
        foreach (array_keys($changes) as $field) {
            $old[$field] = $document->getOriginal($field);
        }

        $action = array_key_exists('status', $changes) && $changes['status'] === 'published'
            ? 'published'
            : 'updated';

        $this->record($document, $action, $old, $changes);
    }

    private function record(PolicyDocument $document, string $action, ?array $old, array $new): void
    {
        DocumentActivityLog::create([
            'policy_document_id' => $document->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => request()?->ip(),
        ]);
    }
}
