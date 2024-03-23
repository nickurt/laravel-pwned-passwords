<?php

namespace nickurt\PwnedPasswords\tests;

use Illuminate\Cache\CacheServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use nickurt\PwnedPasswords\Events\IsPwnedPassword;
use nickurt\PwnedPasswords\Exception\MalformedURLException;
use nickurt\PwnedPasswords\Facade as PwnedPasswords;
use nickurt\PwnedPasswords\ServiceProvider;
use Orchestra\Testbench\TestCase;

class PwnedPasswordsTest extends TestCase
{
    /** @var \nickurt\PwnedPasswords\PwnedPasswords */
    protected $pwnedPassword;

    /**
     * Setup the test environment.
     */
    public function setUp(): void
    {
        parent::setUp();

        /** @var \nickurt\PwnedPasswords\PwnedPasswords */
        $this->pwnedPassword = PwnedPasswords::getFacadeRoot();
    }

    public function test_it_can_return_the_default_values()
    {
        $pwnedPasswords = app('PwnedPasswords');

        $this->assertSame('https://api.pwnedpasswords.com', $pwnedPasswords->getApiUrl());
        $this->assertSame(10, $pwnedPasswords->getFrequency());
    }

    public function test_it_can_set_a_custom_value_for_the_api_url()
    {
        $this->pwnedPassword->setApiUrl('https://api-ppe.pwnedpasswords.com');

        $this->assertSame('https://api-ppe.pwnedpasswords.com', $this->pwnedPassword->getApiUrl());
    }

    public function test_it_can_set_a_custom_value_for_the_frequency()
    {
        $this->pwnedPassword->setFrequency(90);

        $this->assertSame(90, $this->pwnedPassword->getFrequency());
    }

    public function test_it_can_set_a_custom_value_for_the_password()
    {
        $this->pwnedPassword->setPassword('a-erdvspdenasrswawo-llp');

        $this->assertSame('a-erdvspdenasrswawo-llp', $this->pwnedPassword->getPassword());
    }

    public function test_it_can_work_with_helper_function()
    {
        $this->assertInstanceOf(\nickurt\PwnedPasswords\PwnedPasswords::class, pwnedpasswords());
    }

    public function test_it_will_fire_is_pwned_password_event_by_a_pwned_password_via_facade()
    {
        Event::fake();

        Http::fake(['https://api.pwnedpasswords.com/range/8fba5' => Http::response(file_get_contents(__DIR__.'/responses/pwned-passwords-1.txt'))]);

        $this->pwnedPassword->setPassword('qwertyytrewq')->isPwnedPassword();

        Event::assertDispatched(IsPwnedPassword::class, function ($e) {
            $this->assertSame(8185, $e->frequency);
            $this->assertSame('qwertyytrewq', $e->password);

            return true;
        });
    }

    public function test_it_will_fire_is_pwned_password_event_by_a_pwned_password_via_validation_rule()
    {
        Event::fake();

        Http::fake(['https://api.pwnedpasswords.com/range/8fba5' => Http::response(file_get_contents(__DIR__.'/responses/pwned-passwords-1.txt'))]);

        $rule = new \nickurt\PwnedPasswords\Rules\IsPwnedPassword(10);

        $this->assertFalse($rule->passes('password', 'qwertyytrewq'));
        $this->assertSame('This is a pwned password', $rule->message());

        Event::assertDispatched(IsPwnedPassword::class, function ($e) {
            $this->assertSame(8185, $e->frequency);
            $this->assertSame('qwertyytrewq', $e->password);

            return true;
        });
    }

    public function test_it_will_not_fire_is_pwned_password_event_by_a_non_pwned_password_via_facade()
    {
        Event::fake();

        Http::fake(['https://api.pwnedpasswords.com/range/3849a' => Http::response(file_get_contents(__DIR__.'/responses/pwned-passwords-2.txt'))]);

        $this->pwnedPassword->setPassword('laravel-pwned-passwords')->isPwnedPassword();

        Event::assertNotDispatched(IsPwnedPassword::class);
    }

    public function test_it_will_not_fire_is_pwned_password_event_by_a_non_pwned_password_via_validation_rule()
    {
        Event::fake();

        Http::fake(['https://api.pwnedpasswords.com/range/3849a' => Http::response(file_get_contents(__DIR__.'/responses/pwned-passwords-2.txt'))]);

        $rule = new \nickurt\PwnedPasswords\Rules\IsPwnedPassword(10);

        $this->assertTrue($rule->passes('password', 'laravel-pwned-passwords'));

        Event::assertNotDispatched(IsPwnedPassword::class);
    }

    public function test_it_will_throw_pwned_password_exception_if_hash_prefix_was_not_in_a_valid_format()
    {
        // 400 https://api.pwnedpasswords.com/range/invalid

        Http::fake(['https://api.pwnedpasswords.com/range/81f34' => fn () => throw new HttpClientException('The hash prefix was not in a valid format', 400)]);

        $this->expectException(\nickurt\PwnedPasswords\Exception\PwnedPasswordException::class);
        $this->expectExceptionMessage('The hash prefix was not in a valid format');

        $this->pwnedPassword->setPassword('invalid')->isPwnedPassword();
    }

    public function test_it_will_throw_malformed_url_exception()
    {
        $this->expectException(MalformedURLException::class);

        $this->pwnedPassword->setApiUrl('malformed_url');
    }

    /**
     * @param  Application  $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'Cache' => Cache::class,
            'PwnedPasswords' => PwnedPasswords::class,
        ];
    }

    /**
     * @param  Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            CacheServiceProvider::class,
            ServiceProvider::class,
        ];
    }
}
