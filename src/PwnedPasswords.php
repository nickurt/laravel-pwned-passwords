<?php

namespace nickurt\PwnedPasswords;

use \GuzzleHttp\Client;
use \nickurt\PwnedPasswords\Exception\MalformedURLException;

class PwnedPasswords
{
    /** @var string */
    protected $apiUrl = 'https://api.pwnedpasswords.com';

    /** @var \GuzzleHttp\Client */
    protected $client;

    /** @var int */
    protected $frequency = 10;

    /** @var string */
    protected $password;

    /**
     * @return bool
     * @throws \Exception
     */
    public function IsPwnedPassword()
    {
        $password = substr(sha1($this->getPassword()), 0, 5);

        $response = cache()->remember('laravel-pwned-passwords-' . $password, 10, function () use ($password) {
            $response = $this->getResponseData(
                sprintf('%s/range/%s',
                    $this->getApiUrl(),
                    $password
                ));

            return ((string)$response->getBody());
        });

        $lines = explode("\r\n", $response);

        foreach ($lines as $line) {
            list($eHashSuffix, $eFrequency) = explode(':', $line);

            if (strtoupper(substr(sha1($this->getPassword()), 5)) == $eHashSuffix) {
                if ($eFrequency >= $this->getFrequency()) {
                    event(new \nickurt\PwnedPasswords\Events\IsPwnedPassword($this->getPassword()));

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
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param string $url
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getResponseData($url)
    {
        return $this->getClient()->get($url);
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        if (!isset($this->client)) {
            $this->client = new \GuzzleHttp\Client();

            return $this->client;
        }

        return $this->client;
    }

    /**
     * @param $client
     * @return $this
     */
    public function setClient($client)
    {
        $this->client = $client;

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
     * @param string $apiUrl
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
     * @return int
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * @param int $frequency
     * @return $this
     */
    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;

        return $this;
    }
}
