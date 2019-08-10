<?php

namespace nickurt\PwnedPasswords\Rules;

use Exception;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\Validation\Rule;
use PwnedPasswords;

class IsPwnedPassword implements Rule
{
    /** @var int */
    protected $frequency;

    /**
     * IsPwnedPassword constructor.
     * @param int $frequency
     */
    public function __construct($frequency = 10)
    {
        $this->frequency = $frequency;
    }

    /**
     * @return array|Translator|string|null
     */
    public function message()
    {
        return trans('pwned-passwords::pwned-passwords.this_is_a_pwned_password');
    }

    /**
     * @param string $attribute
     * @param mixed $value
     * @return bool
     * @throws Exception
     */
    public function passes($attribute, $value)
    {
        /** @var \nickurt\PwnedPasswords\PwnedPasswords $pwnedPassword */
        $pwnedPassword = PwnedPasswords::setPassword($value)->setFrequency($this->frequency);

        return $pwnedPassword->isPwnedPassword() ? false : true;
    }
}
