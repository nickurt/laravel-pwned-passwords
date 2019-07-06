<?php

namespace nickurt\PwnedPasswords\Events;

class IsPwnedPassword
{
    /** @var string */
    public $password;

    /**
     * @param $password
     */
    public function __construct($password)
    {
        $this->password = $password;
    }
}