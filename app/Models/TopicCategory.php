<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TopicCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'is_active',
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

    public function documents(): HasMany
    {
        return $this->hasMany(PolicyDocument::class, 'topic_category', 'slug');
    }
}
