<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property RoleEnum|null $role
 * @property-read Collection<int, Role> $roles
 * @property-read Collection<int, Permission> $permissions
 */
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'profile_photo_path',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => RoleEnum::class,
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->canAccessAdminSurface()
            && $this->can(PermissionEnum::AccessAdminPanel->value);
    }

    public function canAccessAdminSurface(): bool
    {
        return $this->role?->canAccessAdminSurface() ?? false;
    }

    public function canAccessMerchantSurface(): bool
    {
        return $this->role?->canAccessMerchantSurface() ?? false;
    }

    public function assignPrimaryRole(RoleEnum $role): static
    {
        $this->forceFill(['role' => $role])->save();

        $this->syncRoles([$role->value]);

        return $this;
    }

    public function isStaffAdmin(): bool
    {
        return $this->role === RoleEnum::Admin;
    }

    public function isMerchant(): bool
    {
        return $this->role === RoleEnum::Merchant;
    }

    /**
     * Users who can operate in the admin surface.
     */
    public function isAdmin(): bool
    {
        return $this->canAccessAdminSurface();
    }

    /**
     * @return HasOne<Merchant, $this>
     */
    public function merchant(): HasOne
    {
        return $this->hasOne(Merchant::class);
    }

    /**
     * @return HasMany<UploadJob, $this>
     */
    public function uploadJobs(): HasMany
    {
        return $this->hasMany(UploadJob::class);
    }

    /**
     * @return HasMany<AuditLog, $this>
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}
