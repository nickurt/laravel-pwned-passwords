<?php

use nickurt\PwnedPasswords\PwnedPasswords;

if (!function_exists('pwnedpasswords')) {
    function pwnedpasswords()
    {
        return app(PwnedPasswords::class);
    }
}