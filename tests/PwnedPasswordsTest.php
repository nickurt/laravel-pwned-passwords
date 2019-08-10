<?php

namespace nickurt\PwnedPasswords\Tests;

use Event;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Illuminate\Cache\CacheServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use nickurt\PwnedPasswords\Events\IsPwnedPassword;
use nickurt\PwnedPasswords\Exception\MalformedURLException;
use nickurt\PwnedPasswords\Facade;
use nickurt\PwnedPasswords\ServiceProvider;
use Orchestra\Testbench\TestCase;
use PwnedPasswords;

class PwnedPasswordsTest extends TestCase
{
    /** @var \nickurt\PwnedPasswords\PwnedPasswords */
    protected $pwnedPassword;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        /** @var \nickurt\PwnedPasswords\PwnedPasswords */
        $this->pwnedPassword = PwnedPasswords::getFacadeRoot();
    }

    /** @test */
    public function it_can_get_the_http_client()
    {
        $this->assertInstanceOf(Client::class, $this->pwnedPassword->getClient());
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
        $this->pwnedPassword->setApiUrl('https://api-ppe.pwnedpasswords.com');

        $this->assertSame('https://api-ppe.pwnedpasswords.com', $this->pwnedPassword->getApiUrl());
    }

    /** @test */
    public function it_can_set_a_custom_value_for_the_frequency()
    {
        $this->pwnedPassword->setFrequency(90);

        $this->assertSame(90, $this->pwnedPassword->getFrequency());
    }

    /** @test */
    public function it_can_set_a_custom_value_for_the_password()
    {
        $this->pwnedPassword->setPassword('a-erdvspdenasrswawo-llp');

        $this->assertSame('a-erdvspdenasrswawo-llp', $this->pwnedPassword->getPassword());
    }

    /** @test */
    public function it_can_work_with_helper_function()
    {
        $this->assertInstanceOf(\nickurt\PwnedPasswords\PwnedPasswords::class, pwnedpasswords());
    }

    /** @test */
    public function it_will_fire_is_pwned_password_event_by_a_pwned_password_via_facade()
    {
        Event::fake();

        $this->pwnedPassword->setClient(new Client([
            'handler' => new MockHandler([
                new Response(200, [], file_get_contents(__DIR__ . '/responses/pwned-passwords-1.txt'))
            ]),
        ]))->setPassword('qwertyytrewq')->isPwnedPassword();

        $this->assertSame('https://api.pwnedpasswords.com/range/8fba5', (string)$this->pwnedPassword->getClient()->getConfig()['handler']->getLastRequest()->getUri());

        Event::assertDispatched(IsPwnedPassword::class, function ($e) {
            $this->assertSame(8185, $e->frequency);
            $this->assertSame('qwertyytrewq', $e->password);

            return true;
        });
    }

    /** @test */
    public function it_will_fire_is_pwned_password_event_by_a_pwned_password_via_validation_rule()
    {
        Event::fake();

        $this->pwnedPassword->setClient(new Client([
            'handler' => new MockHandler([
                new Response(200, [], file_get_contents(__DIR__ . '/responses/pwned-passwords-1.txt'))
            ]),
        ]));

        $rule = new \nickurt\PwnedPasswords\Rules\IsPwnedPassword(10);

        $this->assertFalse($rule->passes('password', 'qwertyytrewq'));
        $this->assertSame('This is a pwned password', $rule->message());

        Event::assertDispatched(IsPwnedPassword::class, function ($e) {
            $this->assertSame(8185, $e->frequency);
            $this->assertSame('qwertyytrewq', $e->password);

            return true;
        });
    }

    /** @test */
    public function it_will_not_fire_is_pwned_password_event_by_a_non_pwned_password_via_facade()
    {
        Event::fake();

        $this->pwnedPassword->setClient(new Client([
            'handler' => new MockHandler([
                new Response(200, [], file_get_contents(__DIR__ . '/responses/pwned-passwords-2.txt'))
            ]),
        ]))->setPassword('laravel-pwned-passwords')->isPwnedPassword();

        $this->assertSame('https://api.pwnedpasswords.com/range/3849a', (string)$this->pwnedPassword->getClient()->getConfig()['handler']->getLastRequest()->getUri());

        Event::assertNotDispatched(IsPwnedPassword::class);
    }

    /** @test */
    public function it_will_not_fire_is_pwned_password_event_by_a_non_pwned_password_via_validation_rule()
    {
        Event::fake();

        $this->pwnedPassword->setClient(new Client([
            'handler' => new MockHandler([
                new Response(200, [], file_get_contents(__DIR__ . '/responses/pwned-passwords-2.txt'))
            ]),
        ]));

        $rule = new \nickurt\PwnedPasswords\Rules\IsPwnedPassword(10);

        $this->assertTrue($rule->passes('password', 'laravel-pwned-passwords'));

        Event::assertNotDispatched(IsPwnedPassword::class);
    }

    /** @test */
    public function it_will_throw_pwned_password_exception_if_hash_prefix_was_not_in_a_valid_format()
    {
        // 400 https://api.pwnedpasswords.com/range/invalid

        $this->expectException(\nickurt\PwnedPasswords\Exception\PwnedPasswordException::class);
        $this->expectExceptionMessage('The hash prefix was not in a valid format');

        $this->pwnedPassword->setClient(new Client([
            'handler' => MockHandler::createWithMiddleware([
                new Response(400, [], 'The hash prefix was not in a valid format')
            ]),
        ]));

        $this->pwnedPassword->setPassword('invalid')->isPwnedPassword();
    }

    /** @test */
    public function it_will_throw_malformed_url_exception()
    {
        $this->expectException(MalformedURLException::class);

        $this->pwnedPassword->setApiUrl('malformed_url');
    }

    /**
     * @param Application $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'Cache' => Cache::class,
            'PwnedPasswords' => Facade::class
        ];
    }

    /**
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            CacheServiceProvider::class,
            ServiceProvider::class
        ];
    }
}
