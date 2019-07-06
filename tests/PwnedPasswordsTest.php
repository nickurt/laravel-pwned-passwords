<?php

namespace nickurt\PwnedPasswords\Tests;

use Orchestra\Testbench\TestCase;
use PwnedPasswords;

class PwnedPasswordsTest extends TestCase
{
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

    /** @test */
    public function it_can_get_the_http_client()
    {
        $this->assertInstanceOf(\GuzzleHttp\Client::class, \PwnedPasswords::getClient());
    }

    /** @test */
    public function it_can_return_the_default_values()
    {
        $pwnedPasswords = app('PwnedPasswords');

        $this->assertSame('https://api.pwnedpasswords.com', $pwnedPasswords->getApiUrl());
        $this->assertSame(10, $pwnedPasswords->getFrequency());
    }

    /** @test */
    public function it_can_set_a_custom_value_for_the_api_url()
    {
        $pwnedPasswords = \PwnedPasswords::setApiUrl('https://api-ppe.pwnedpasswords.com');

        $this->assertSame('https://api-ppe.pwnedpasswords.com', $pwnedPasswords->getApiUrl());
    }

    /** @test */
    public function it_can_set_a_custom_value_for_the_frequency()
    {
        $pwnedPasswords = \PwnedPasswords::setFrequency(90);

        $this->assertSame(90, $pwnedPasswords->getFrequency());
    }

    /** @test */
    public function it_can_set_a_custom_value_for_the_password()
    {
        $pwnedPasswords = \PwnedPasswords::setPassword('a-erdvspdenasrswawo-llp');

        $this->assertSame('a-erdvspdenasrswawo-llp', $pwnedPasswords->getPassword());
    }

    /** @test */
    public function it_can_work_with_helper_function()
    {
        $this->assertInstanceOf(\nickurt\PwnedPasswords\PwnedPasswords::class, pwnedpasswords());
    }

    /** @test */
    public function it_will_fire_is_pwned_password_event_by_a_pwned_password_via_facade()
    {
        \Event::fake();

        \PwnedPasswords::setClient(new \GuzzleHttp\Client([
            'handler' => new \GuzzleHttp\Handler\MockHandler([
                new \GuzzleHttp\Psr7\Response(200, [], file_get_contents(__DIR__ . '/responses/pwned-passwords-1.txt'))
            ]),
        ]))->setPassword('qwertyytrewq')->isPwnedPassword();

        \Event::assertDispatched(\nickurt\PwnedPasswords\Events\IsPwnedPassword::class, function ($e) {
            return ($e->password == 'qwertyytrewq');
        });
    }

    /** @test */
    public function it_will_fire_is_pwned_password_event_by_a_pwned_password_via_validation_rule()
    {
        \Event::fake();

        \PwnedPasswords::setClient(new \GuzzleHttp\Client([
            'handler' => new \GuzzleHttp\Handler\MockHandler([
                new \GuzzleHttp\Psr7\Response(200, [], file_get_contents(__DIR__ . '/responses/pwned-passwords-1.txt'))
            ]),
        ]));

        $rule = new \nickurt\PwnedPasswords\Rules\IsPwnedPassword(10);

        $this->assertFalse($rule->passes('password', 'qwertyytrewq'));

        \Event::assertDispatched(\nickurt\PwnedPasswords\Events\IsPwnedPassword::class, function ($e) {
            return ($e->password == 'qwertyytrewq');
        });
    }

    /** @test */
    public function it_will_not_fire_is_pwned_password_event_by_a_non_pwned_password_via_facade()
    {
        \Event::fake();

        \PwnedPasswords::setClient(new \GuzzleHttp\Client([
            'handler' => new \GuzzleHttp\Handler\MockHandler([
                new \GuzzleHttp\Psr7\Response(200, [], file_get_contents(__DIR__ . '/responses/pwned-passwords-2.txt'))
            ]),
        ]))->setPassword('laravel-pwned-passwords')->isPwnedPassword();

        \Event::assertNotDispatched(\nickurt\PwnedPasswords\Events\IsPwnedPassword::class);
    }

    /** @test */
    public function it_will_not_fire_is_pwned_password_event_by_a_non_pwned_password_via_validation_rule()
    {
        \Event::fake();

        \PwnedPasswords::setClient(new \GuzzleHttp\Client([
            'handler' => new \GuzzleHttp\Handler\MockHandler([
                new \GuzzleHttp\Psr7\Response(200, [], file_get_contents(__DIR__ . '/responses/pwned-passwords-2.txt'))
            ]),
        ]));

        $rule = new \nickurt\PwnedPasswords\Rules\IsPwnedPassword(10);

        $this->assertTrue($rule->passes('password', 'laravel-pwned-passwords'));

        \Event::assertNotDispatched(\nickurt\PwnedPasswords\Events\IsPwnedPassword::class);
    }

    /** @test */
    public function it_will_throw_malformed_url_exception()
    {
        $this->expectException(\nickurt\PwnedPasswords\Exception\MalformedURLException::class);

        \PwnedPasswords::setApiUrl('malformed_url');
    }
}