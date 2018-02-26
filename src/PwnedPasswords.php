<?php

namespace nickurt\PwnedPasswords;

use \GuzzleHttp\Client;
use \nickurt\PwnedPasswords\Exception\MalformedURLException;

class PwnedPasswords
{
    /**
     * @var string
     */
    protected $apiUrl = 'https://api.pwnedpasswords.com';

    /**
     * @var
     */
    protected $password;

    /**
     * @var
     */
    protected $frequency = 10;

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * @param $apiUrl
     * @return $this
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
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * @param $frequency
     * @return $this
     */
    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;
        return $this;
    }

    /**
     * @return bool
     */
    public function IsPwnedPassword()
    {
        $password = substr(sha1($this->getPassword()), 0, 5);

        $response = cache()->remember('laravel-pwned-passwords-'.$password, 10, function () use ($password) {
            $response = $this->getResponseData(
                sprintf('%s/range/%s',
                    $this->getApiUrl(),
                    $password
                ));
    
            return ((string) $response->getBody());
        });

        $lines = explode("\r\n", $response);
        
        foreach($lines as $line){
            list($eHashSuffix, $eFrequency) = explode(':', $line);

            if (strtoupper(substr(sha1($this->getPassword()), 5)) == $eHashSuffix) {
                return (bool) ($eFrequency >= $this->getFrequency());
            }
        }

        return false;
    }

    /**
     * @param $url
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getResponseData($url)
    {
        return (new Client())->get($url);
    }
}
