<?php

namespace nickurt\PwnedPasswords\Tests;

use Orchestra\Testbench\TestCase;
use PwnedPasswords;

class PwnedPasswordsTest extends TestCase
{
    /** @test */
    public function test_it_can_get_default_values()
    {
        $pwnedPasswords = new \nickurt\PwnedPasswords\PwnedPasswords();

        $this->assertSame('https://api.pwnedpasswords.com', $pwnedPasswords->getApiUrl());
        $this->assertNull($pwnedPasswords->getPassword());
        $this->assertSame(10, $pwnedPasswords->getFrequency());
    }

    /** @test */
    public function test_it_can_set_custom_values()
    {
        $pwnedPasswords = (new \nickurt\PwnedPasswords\PwnedPasswords())
            ->setApiUrl('https://internal.api.pwnedpasswords.com')
            ->setPassword('administrator')
            ->setFrequency(100);

        $this->assertSame('https://internal.api.pwnedpasswords.com', $pwnedPasswords->getApiUrl());
        $this->assertSame('administrator', $pwnedPasswords->getPassword());
        $this->assertSame(100, $pwnedPasswords->getFrequency());
    }

    /** @test */
    public function test_it_can_work_with_helper()
    {
        $this->assertTrue(function_exists('pwnedpasswords'));

        $this->assertInstanceOf(\nickurt\PwnedPasswords\PwnedPasswords::class, pwnedpasswords());
    }

    /** @test */
    public function test_it_can_work_with_container()
    {
        $this->assertInstanceOf(\nickurt\PwnedPasswords\PwnedPasswords::class, $this->app['PwnedPasswords']);
    }

    /** @test */
    public function test_it_can_work_with_facade()
    {
        $this->assertSame('nickurt\PwnedPasswords\Facade', (new \ReflectionClass(PwnedPasswords::class))->getName());

        $this->assertSame('https://api.pwnedpasswords.com', PwnedPasswords::getApiUrl());
        $this->assertNull(PwnedPasswords::getPassword());
        $this->assertSame(10, PwnedPasswords::getFrequency());
    }

    /** @test */
    public function test_it_will_work_with_validation_rule()
    {
        $val1 = $this->validate('administrator', 1000);

        $this->assertFalse($val1->passes());
        $this->assertSame(1, count($val1->messages()->get('password')));
        $this->assertSame('This is a pwned password', $val1->messages()->first('password'));

        $val2 = $this->validate('administrator', 17600);

        $this->assertTrue($val2->passes());
        $this->assertSame(0, count($val2->messages()->get('password')));

        $val3 = $this->validate('', 1000);

        $this->assertFalse($val3->passes());
        $this->assertSame(1, count($val3->messages()->get('password')));
        $this->assertSame('The password field is required.', $val3->messages()->first('password'));
    }

    /**
     * @test
     * @expectedException \nickurt\PwnedPasswords\Exception\MalformedURLException
     */
    public function test_it_will_throw_malformed_url_exception()
    {
        $pwnedPasswords = (new \nickurt\PwnedPasswords\PwnedPasswords())
            ->setApiUrl('malformed_url');
    }

    /**
     * @param $password
     * @param $frequency
     * @return mixed
     */
    protected function validate($password, $frequency)
    {
        return \Validator::make(
            ['password' => $password],
            ['password' => ['required', new \nickurt\PwnedPasswords\Rules\IsPwnedPassword(
                $password, $frequency
            )]]
        );
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'Cache' => \Illuminate\Support\Facades\Cache::class,
            'PwnedPasswords' => \nickurt\PwnedPasswords\Facade::class
        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \Illuminate\Cache\CacheServiceProvider::class,
            \nickurt\PwnedPasswords\ServiceProvider::class
        ];
    }
}