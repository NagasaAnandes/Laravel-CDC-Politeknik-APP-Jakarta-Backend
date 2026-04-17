<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',

        'role',
        'is_active',
        'company_id',

        'phone',
        'linkedin_url',
        'graduation_year',
        'program_study',
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
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    protected static function booted()
    {
        static::saving(function ($user) {

            if ($user->role?->isAdmin()) {
                $user->company_id = null;
            }

            if ($user->role === UserRole::COMPANY && ! $user->company_id) {
                throw new \LogicException(
                    'Company user must have company_id.'
                );
            }
        });

        static::updating(function ($user) {

            if ($user->isDirty('role')) {
                throw new \LogicException('Role cannot be changed directly.');
            }

            if ($user->isDirty('is_active')) {
                throw new \LogicException('Status cannot be changed directly.');
            }
        });
    }

    /**
     * Check if user is admin (super_admin or admin_cdc).
     */

    public function isAdmin(): bool
    {
        return $this->role?->isAdmin() === true;
    }

    public function isStudentOrAlumni(): bool
    {
        return in_array($this->role, [
            UserRole::STUDENT,
            UserRole::ALUMNI,
        ]);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(Education::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * Check if user status is active.
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->isActive() && $this->role?->isAdmin(),
            'partner' => $this->isActive() && $this->role === UserRole::COMPANY,
            default => false,
        };
    }

    /**
     * Determine if user can access another user's profile.
     *
     * NOTE:
     * - Currently owner + admin only (MVP mode)
     * - Company access will be added after job_applications is implemented
     */
    public function canAccessUserProfile(int $targetUserId): bool
    {
        // Owner
        if ($this->id === $targetUserId) {
            return true;
        }

        // Admin
        if ($this->isAdmin() && $this->isActive()) {
            return true;
        }

        // Company (Controlled Access)

        return false;
    }
}
