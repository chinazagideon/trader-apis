<?php

namespace App\Core\Contracts;

interface SubModuleServiceContract
{

    /**
     * Get the default sub module name
     */
    public function getDefaultSubModuleName(): string;


}
