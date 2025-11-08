<?php

namespace App\Core\Models;

use App\Core\Traits\HasClientApp;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class ClientScope implements Scope
{
    use HasClientApp;
    /**
     * apply the scope
     *
     * @param Builder $builder
     * @param Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if (!$this->hasClient()) {
            return;
        }

        // only if the model has client_id column
        if (Schema::hasColumn($model->getTable(), 'client_id')) {
            $builder->where($model->getTable() . '.client_id', app('current_client_id'));
        }
    }
}
