<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PolicyDocument extends Model
{
    use HasFactory;

    protected $table = 'hr_intern.policy_documents';

    protected $fillable = [
        'title',
        'reference_number',
        'document_type',
        'topic_category',
        'subtopic_id',
        'topic_detail_id',
        'content',
        'revision_summary',
        'effective_date',
        'expiry_date',
        'remarks',
        'access_scope',
        'public_flag',
        'owner_unit',
        'owner_report',
        'status',
        'is_circular',
        'version_number',
        'parent_document_id',
        'created_by',
        'updated_by',
        'file_path',
        'file_original_name',
        'published_at',
        'published_by',
    ];

    protected function casts(): array
    {
        return [
            'is_circular' => 'boolean',
            'public_flag' => 'boolean',
            'published_at' => 'datetime',
            'effective_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_document_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function subtopic(): BelongsTo
    {
        return $this->belongsTo(TopicSubtopic::class, 'subtopic_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(self::class, 'parent_document_id');
    }

    public function topicDetail(): BelongsTo
    {
        return $this->belongsTo(TopicDetail::class, 'topic_detail_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(DocumentActivityLog::class)->latest();
    }

    public function histories(): HasMany
    {
        return $this->hasMany(DocumentHistory::class)->orderByDesc('version_number');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(DocumentAttachment::class)->latest();
    }

    public function formResponses(): HasMany
    {
        return $this->hasMany(DocumentFormResponse::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'published' => 'Active',
            'superseded' => 'Superceded',
            default => ucfirst((string) $this->status),
        };
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $user || ! $user->is_active) {
            return $query->published()->where('access_scope', 'all');
        }

        if ($user->canManagePolicies()) {
            return $query;
        }

        return $query->published()->where(function (Builder $builder) use ($user): void {
            $builder->where('access_scope', 'all');

            if (in_array($user->unit, ['msd', 'kcdiom'], true)) {
                $builder->orWhere('access_scope', $user->unit);
            }
        });
    }
}
