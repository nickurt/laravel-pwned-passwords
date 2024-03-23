<?php

namespace nickurt\PwnedPasswords\Events;

class IsPwnedPassword
{
    /** @var string */
    public $password;

    /** @var int */
    public $frequency;

    /**
     * @param  string  $password
     */
    public function __construct($password, int $frequency)
    {
        $this->password = $password;
        $this->frequency = $frequency;
    }
}
