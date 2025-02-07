<?php

namespace PavelZanek\LaravelGoPaySDK;

use Illuminate\Support\Facades\Facade as LaravelFacade;

class GoPaySDKFacade extends LaravelFacade
{
    protected static function getFacadeAccessor() { return 'GoPaySDK'; }
}