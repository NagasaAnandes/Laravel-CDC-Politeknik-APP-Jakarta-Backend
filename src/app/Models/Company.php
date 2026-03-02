<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Company extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'industry',
        'website',
        'email_contact',
        'phone',
        'address',
        'description',
        'logo_path',
        'is_active',
        'approved_at',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'approved_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot Logic
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        // Auto slug on create
        static::creating(function ($company) {
            if (! $company->slug) {
                $company->slug = static::generateUniqueSlug($company->name);
            }
        });

        // Ensure slug uniqueness on update
        static::updating(function ($company) {
            if ($company->isDirty('name') && ! $company->isDirty('slug')) {
                $company->slug = static::generateUniqueSlug($company->name, $company->id);
            }

            // Delete old logo if replaced
            if ($company->isDirty('logo_path')) {
                $old = $company->getOriginal('logo_path');

                if ($old) {
                    Storage::disk('public')->delete($old);
                }
            }
        });

        // Delete logo on delete
        static::deleting(function ($company) {
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Slug Generator
    |--------------------------------------------------------------------------
    */

    protected static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $counter = 1;

        while (
            static::where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = "{$original}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path
            ? Storage::url($this->logo_path)
            : null;
    }
}
