<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentAttachment extends Model
{
    protected $fillable = [
        'policy_document_id', 'document_history_id', 'file_name', 'file_path',
        'file_size', 'file_type', 'checksum_sha256', 'security_status',
        'is_public', 'integrity_verified_at', 'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'integrity_verified_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo { return $this->belongsTo(PolicyDocument::class, 'policy_document_id'); }
    public function history(): BelongsTo { return $this->belongsTo(DocumentHistory::class, 'document_history_id'); }
    public function uploader(): BelongsTo { return $this->belongsTo(User::class, 'uploaded_by'); }
}
