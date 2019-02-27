<?php

namespace nickurt\PwnedPasswords\Rules;

use Illuminate\Contracts\Validation\Rule;

class IsPwnedPassword implements Rule
{
    /**
     * @var
     */
    protected $password;

    /**
     * @var
     */
    protected $frequency;

    /**
     * Create a new rule instance.
     *
     * @param $password
     * @param $frequency
     *
     * @return void
     */
    public function __construct($password, $frequency = 10)
    {
        $this->password = $password;
        $this->frequency = $frequency;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('pwned-passwords::pwned-passwords.this_is_a_pwned_password');
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $pwnedPassword = (new \nickurt\PwnedPasswords\PwnedPasswords())
            ->setPassword($this->password)
            ->setFrequency($this->frequency);

        return $pwnedPassword->isPwnedPassword() ? false : true;
    }
}
