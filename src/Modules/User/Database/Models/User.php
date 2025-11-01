<?php

namespace App\Modules\User\Database\Models;

use App\Core\Traits\HasPermissions;
use App\Core\Traits\HasTimestamps;
use App\Core\Traits\HasUuid;
use App\Modules\User\Enums\RolesEnum;
use App\Modules\Transaction\Traits\HasTransactableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasTimestamps, HasUuid, HasTransactableTrait;
    use HasPermissions;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'phone',
        'date_of_birth',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'is_active',
        'email_verified_at',
        'email_verification_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for verified users
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Scope for search
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%")
                ->orWhere('phone', 'LIKE', "%{$search}%");
        });
    }

    /**
     * Get the user's auth tokens
     */
    public function authTokens()
    {
        return $this->hasMany(\App\Modules\Auth\Database\Models\AuthToken::class);
    }

    public function hasPermission(string $permission, array $actions = []): bool
    {
        if ($this->role_id === RolesEnum::ADMIN->value) { // Admin
            return true;
        }

        if ($this->role_id === RolesEnum::MODERATOR->value) { // Moderator
            return in_array(
                $permission,
                array_merge($actions, [
                    'view',
                    'create',
                    'update',
                    'delete',
                    'view_any',
                    'create_any',
                    'update_any',
                    'delete_any',
                ])
            )
                ? true : false;
        }

        return false;
    }

    public function getOwnershipColumn(): string
    {
        return 'id';
    }
}
