<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Organization extends Model
{
    protected $fillable = [
        'code',
        'name',
        'organization_type',
        'parent_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public static function idForLegacyUnit(string $unit): ?int
    {
        $code = strtolower($unit) === 'kcdiom' ? 'KCDIOM' : 'MSD';

        return static::query()->where('code', $code)->value('id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('name');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PolicyDocument::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(OrganizationProfile::class);
    }
}
