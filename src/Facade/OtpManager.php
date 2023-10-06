<?php

namespace Salehhashemi\OtpManager\Facade;

use Illuminate\Support\Facades\Facade;

class OtpManager extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return \Salehhashemi\OtpManager\OtpManager::class;
    }
}
