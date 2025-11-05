<?php

namespace App\Core\Traits;

use Illuminate\Database\Eloquent\Model;

trait AuthorizesShowRequest
{
    /**
     * Get the model class name
     * Must be implemented by using class
     */
    abstract protected function getModelClass(): string;

    /**
     * Get the route parameter name (default: 'id')
     */
    protected function getRouteParameterName(): string
    {
        return 'id';
    }

    /**
     * Authorize show request by checking instance-level permissions
     */
    protected function authorizeShowRequest(): bool
    {
        if (!$this->user()) {
            return false;
        }

        $parameterValue = $this->route($this->getRouteParameterName());

        if (!$parameterValue) {
            return false;
        }

        $model = $this->findModelForShow($parameterValue);

        if (!$model) {
            return false;
        }

        return $this->user()->can('view', $model);
    }

    protected function findModelForShow($value): ?Model
    {
        $modelClass = $this->getModelClass();

        if ($this->getRouteParameterName() === 'id') {
            return $modelClass::find($value);
        }

        return $modelClass::where($this->getRouteParameterName(), $value)->first();
    }
}
