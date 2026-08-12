<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopicCategory extends Model
{
    use HasFactory;

    protected $table = 'hr_intern.topic_categories';

    protected $fillable = [
        'name',
        'slug',
        'owner_unit',
        'organization_id',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function subtopics(): HasMany
    {
        return $this->hasMany(TopicSubtopic::class, 'topic_category_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PolicyDocument::class, 'topic_category', 'slug');
    }
}
