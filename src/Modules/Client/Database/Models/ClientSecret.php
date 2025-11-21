<?php

namespace App\Modules\Client\Database\Models;

use App\Core\Models\CoreModel;
use App\Core\Traits\HasTimestamps;
use App\Core\Traits\HasUuid;
use App\Core\Traits\HasClientScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientSecret extends CoreModel
{
    use HasTimestamps, HasUuid;
    use HasClientScope;

    /**
     * The table associated with the model.
     *
     * @return string
     */
    protected $table = 'client_secrets';

    /**
     * The attributes that are mass assignable.
     *
     * @return array
     */
    protected $fillable = [
        'uuid',
        'client_id',
        'module_name', //abstract module name example: withdrawal, payment, funding, etc.
        'secrets', //array of secrets example: ['secret1', 'secret2', 'secret3']
        'action', //action name example: mail, sms, push, etc.
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'secrets' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the client that owns the client secret.
     *
     * @return BelongsTo
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
    /**
     * Scope to filter by action
     */
    public function scopeByAction($query, ?string $action)
    {
        if ($action === null) {
            return $query->whereNull('action');
        }
        return $query->where('action', $action);
    }

    /**
     * Scope to get secrets for a module and action
     */
    public function scopeForModuleAndAction($query, string $moduleName, ?string $action = null)
    {
        return $query->where('module_name', $moduleName)
            ->byAction($action);
    }
}
