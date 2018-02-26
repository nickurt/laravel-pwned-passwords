<?php

namespace nickurt\PwnedPasswords;

class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'PwnedPasswords';
    }
}
