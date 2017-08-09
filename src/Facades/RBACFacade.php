<?php

namespace lroman242\LaravelRBAC\Facades;

use Illuminate\Support\Facades\Facade;

class RBAC extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'RBAC';
    }
}