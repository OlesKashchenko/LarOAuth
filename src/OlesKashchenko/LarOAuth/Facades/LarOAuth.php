<?php

namespace OlesKashchenko\LarOAuth\Facades;

use Illuminate\Support\Facades\Facade;


class LarOAuth extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'lar_oauth';
    } // end getFacadeAccessor

}