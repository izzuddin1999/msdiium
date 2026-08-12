<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormTemplate extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'document_type', 'owner_unit', 'organization_id', 'columns', 'is_active', 'created_by'];

    protected function casts(): array { return ['is_active' => 'boolean', 'columns' => 'integer']; }
    public function fields(): HasMany { return $this->hasMany(FormField::class)->orderBy('sort_order')->orderBy('id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function responses(): HasMany { return $this->hasMany(DocumentFormResponse::class); }
}
