<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentHistory extends Model
{
    protected $fillable = ['policy_document_id', 'version_number', 'status', 'revision_summary', 'created_by', 'published_at'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    public function document(): BelongsTo { return $this->belongsTo(PolicyDocument::class, 'policy_document_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function attachments(): HasMany { return $this->hasMany(DocumentAttachment::class); }
}
