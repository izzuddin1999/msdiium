<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentFormResponse extends Model
{
    protected $fillable = ['policy_document_id', 'form_template_id', 'values', 'submitted_by'];
    protected function casts(): array { return ['values' => 'array']; }
    public function template(): BelongsTo { return $this->belongsTo(FormTemplate::class, 'form_template_id'); }
    public function document(): BelongsTo { return $this->belongsTo(PolicyDocument::class, 'policy_document_id'); }
}
