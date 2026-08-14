<?php

namespace App\Models;

use App\Models\Concerns\UsesHrInternSchema;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PolicyDocument extends Model
{
    use UsesHrInternSchema;
    use HasFactory;

    protected $table = 'hr_intern.policy_documents';

    public function getTable()
    {
        // The local SQLite workspace attaches the same database under the
        // production schema name for read compatibility. Writes must use the
        // main table name so observers can save history and audit rows without
        // SQLite treating the two names as competing database connections.
        return config('database.default') === 'sqlite'
            ? 'policy_documents'
            : parent::getTable();
    }

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
        'organization_id',
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
        'ai_summary',
        'ai_summary_status',
        'ai_summary_model',
        'ai_summary_source_hash',
        'ai_summary_generated_at',
        'ai_summary_generated_by',
        'ai_summary_approved_at',
        'ai_summary_approved_by',
    ];

    protected function casts(): array
    {
        return [
            'is_circular' => 'boolean',
            'public_flag' => 'boolean',
            'published_at' => 'datetime',
            'effective_date' => 'date',
            'expiry_date' => 'date',
            'ai_summary_generated_at' => 'datetime',
            'ai_summary_approved_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
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

    public function aiSummaryGenerator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ai_summary_generated_by');
    }

    public function aiSummaryApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ai_summary_approved_by');
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

    /** Keep one current row per document family for dashboards and repositories. */
    public function scopeLatestInFamily(Builder $query, ?array $statuses = null): Builder
    {
        return $query->whereNotExists(function ($newer) use ($statuses): void {
            $newer->selectRaw('1')
                ->from((new self)->getTable().' as newer_family_version')
                ->whereRaw('COALESCE(newer_family_version.parent_document_id, newer_family_version.id) = COALESCE(policy_documents.parent_document_id, policy_documents.id)')
                ->whereColumn('newer_family_version.version_number', '>', 'policy_documents.version_number');

            if ($statuses !== null) {
                $newer->whereIn('newer_family_version.status', $statuses);
            }
        });
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
            if ($user->isSystemAdmin()) {
                return $query;
            }

            return $query->where(function (Builder $builder) use ($user): void {
                if ($user->organization_id) {
                    $builder->where('organization_id', $user->organization_id)
                        ->orWhere(fn (Builder $legacy) => $legacy->whereNull('organization_id')->where('owner_unit', $user->unit));
                } else {
                    $builder->where('owner_unit', $user->unit === 'kcdiom' ? 'kcdiom' : 'msd');
                }
            });
        }

        return $query->whereIn('status', ['published', 'superseded'])->where(function (Builder $builder) use ($user): void {
            $builder->where('access_scope', 'all');

            if (in_array($user->unit, ['msd', 'kcdiom'], true)) {
                $builder->orWhere('access_scope', $user->unit);
            }
        });
    }
}
