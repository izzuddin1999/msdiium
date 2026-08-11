<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TopicSubtopic extends Model
{
    use HasFactory;

    protected $table = 'hr_intern.topic_subtopics';

    protected $fillable = [
        'topic_category_id',
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

    public function mainTopic(): BelongsTo
    {
        return $this->belongsTo(TopicCategory::class, 'topic_category_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(TopicDetail::class, 'main_topic_id');
    }
}
