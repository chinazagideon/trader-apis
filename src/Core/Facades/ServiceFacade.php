<?php

namespace App\Core\Facades;

use Illuminate\Support\Facades\Facade;

class ServiceFacade extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'service';
    }
}
