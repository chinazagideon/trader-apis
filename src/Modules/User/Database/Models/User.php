<?php

namespace App\Modules\User\Database\Models;

use App\Core\Traits\HasClientApp;
use App\Core\Traits\HasClientScope;
use App\Core\Traits\HasPermissions;
use App\Core\Traits\HasTimestamps;
use App\Core\Traits\HasUuid;
use App\Modules\User\Enums\RolesEnum;
use App\Modules\Transaction\Traits\HasTransactableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Role\Database\Models\Role;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Modules\Payment\Traits\HasPayments;
use App\Modules\Client\Database\Models\Client;
class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens;
    use HasPermissions;
    use HasFactory;
    use Notifiable;
    use HasTimestamps;
    use HasUuid;
    use HasTransactableTrait;
    use HasClientApp;
    use HasClientScope;
    use HasPayments;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'client_id',
        'name',
        'first_name',
        'last_name',
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
        'user_type',
        'referral_code',
        'email_verified_at',
        'email_verification_token',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_token',
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
            'role_id' => 'integer',
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

    /**
     * Check if the user has a permission.
     * @param string $permission
     * @param array $actions
     * @return bool
     */
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

    /**
     * Get the ownership column.
     * @return string
     */
    public function getOwnershipColumn(): string
    {
        return 'id';
    }

    /**
     * Get the role that owns the user.
     * @return BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * get the jwt identifier
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * custom jwt claims
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'client_id' => $this->getClientId() ?? $this->client_id,
            'role_id' => $this->role_id,
            'user_id' => $this->id,
        ];
    }

    /**
     * Check if user has role
     * @param int $roleId
     * @return bool
     */
    public function hasRole(int $roleId): bool
    {
        return $this->role_id === $roleId;
    }

    /**
     * Get the client that owns the user.
     * @return BelongsTo
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * is admin User
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role_id === RolesEnum::ADMIN->value;
    }
}
