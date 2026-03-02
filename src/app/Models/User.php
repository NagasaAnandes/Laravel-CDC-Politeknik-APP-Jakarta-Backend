<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function isAdmin(): bool
    {
        return $this->role?->isAdmin() === true;
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
}
