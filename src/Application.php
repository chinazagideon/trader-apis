<?php

namespace App;

use Illuminate\Foundation\Application as BaseApplication;

class Application extends BaseApplication
{
    /**
     * Get the application namespace.
     *
     * @return string
     */
    public function getNamespace()
    {
        return 'App\\';
    }
}
