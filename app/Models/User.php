<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use App\Models\PolicyDocument;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'hr_intern.users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'staff_id',
        'cas_username',
        'email',
        'role',
        'unit',
        'organization_id',
        'is_active',
        'last_cas_sync_at',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'last_cas_sync_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function hasRole(string $role): bool
    {
        return $this->is_active && $this->role === $role;
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function organizationCode(): string
    {
        return strtoupper((string) ($this->organization?->code ?: ($this->unit === 'kcdiom' ? 'KCDIOM' : 'MSD')));
    }

    public function isPolicyManager(): bool
    {
        return $this->is_active && in_array($this->role, ['system_admin', 'policy_manager', 'msd_admin', 'kcdiom_liaison'], true);
    }

    public function isSystemAdmin(): bool
    {
        return $this->is_active && $this->role === 'system_admin';
    }

    public function canAdministerAccess(): bool
    {
        return $this->isSystemAdmin();
    }

    public function isViewerActor(): bool
    {
        return ! $this->isPolicyManager() && $this->is_active;
    }

    public function actorLabel(): string
    {
        if ($this->role === 'msd_admin') {
            return 'MSD Administrator';
        }

        if ($this->isSystemAdmin()) {
            return 'System Administrator';
        }

        if ($this->isKcdiomLiaison()) {
            return 'KCDIOM Policy Manager';
        }

        if ($this->isPolicyManager()) {
            return $this->organizationCode().' Policy Manager';
        }

        return 'Staff User';
    }

    public function isMsdAdmin(): bool
    {
        return $this->is_active && $this->role === 'msd_admin';
    }

    public function isKcdiomLiaison(): bool
    {
        return $this->isPolicyManager() && $this->unit === 'kcdiom';
    }

    public function canManagePolicies(): bool
    {
        return $this->isPolicyManager();
    }

    public function canReceiveCircularNotificationFor(PolicyDocument $policyDocument): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($policyDocument->access_scope === 'all') {
            return true;
        }

        return $this->unit === $policyDocument->access_scope;
    }
}
