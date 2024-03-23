<?php

namespace nickurt\PwnedPasswords;

use Exception;
use Illuminate\Support\Facades\Http;
use nickurt\PwnedPasswords\Events\IsPwnedPassword;
use nickurt\PwnedPasswords\Exception\MalformedURLException;
use nickurt\PwnedPasswords\Exception\PwnedPasswordException;

class PwnedPasswords
{
    /** @var string */
    protected $apiUrl = 'https://api.pwnedpasswords.com';

    /** @var int */
    protected $frequency = 10;

    /** @var string */
    protected $password;

    /**
     * @return bool
     *
     * @throws Exception
     */
    public function isPwnedPassword()
    {
        $password = substr(sha1($this->getPassword()), 0, 5);

        $response = cache()->remember('laravel-pwned-passwords-'.$password, 10, function () use ($password) {
            try {
                $response = Http::get($this->getApiUrl().'/range/'.$password);
            } catch (\Exception $e) {
                throw new PwnedPasswordException($e->getMessage());
            }

            return $response->body();
        });

        $lines = explode("\r\n", $response);

        foreach ($lines as $line) {
            [$eHashSuffix, $eFrequency] = explode(':', $line);

            if (strtoupper(substr(sha1($this->getPassword()), 5)) == $eHashSuffix) {
                if ($eFrequency >= $this->getFrequency()) {
                    event(new IsPwnedPassword($this->getPassword(), $eFrequency));

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param  string  $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * @param  string  $apiUrl
     * @return $this
     *
     * @throws MalformedURLException
     */
    public function setApiUrl($apiUrl)
    {
        if (filter_var($apiUrl, FILTER_VALIDATE_URL) === false) {
            throw new MalformedURLException();
        }

        $this->apiUrl = $apiUrl;

        return $this;
    }

    /**
     * @return int
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * @param  int  $frequency
     * @return $this
     */
    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;

        return $this;
    }
}
