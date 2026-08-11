<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopicDetail extends Model
{
    protected $fillable = ['main_topic_id', 'name', 'slug', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function mainTopic(): BelongsTo
    {
        return $this->belongsTo(TopicSubtopic::class, 'main_topic_id');
    }
}
