<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormField extends Model
{
    protected $fillable = ['form_template_id', 'label', 'name', 'binding', 'type', 'section', 'width', 'is_required', 'placeholder', 'help_text', 'default_value', 'options', 'data_source', 'validation', 'sort_order'];
    protected function casts(): array { return ['is_required' => 'boolean', 'options' => 'array', 'validation' => 'array', 'width' => 'integer']; }
    public function template(): BelongsTo { return $this->belongsTo(FormTemplate::class, 'form_template_id'); }
}
