## Laravel Pwned Passwords
[![Build Status](https://github.com/nickurt/laravel-pwned-passwords/workflows/tests/badge.svg)](https://github.com/nickurt/laravel-pwned-passwords/actions)
[![Total Downloads](https://poser.pugx.org/nickurt/laravel-pwned-passwords/d/total.svg)](https://packagist.org/packages/nickurt/laravel-plesk)
[![Latest Stable Version](https://poser.pugx.org/nickurt/laravel-pwned-passwords/v/stable.svg)](https://packagist.org/packages/nickurt/laravel-plesk)
[![MIT Licensed](https://poser.pugx.org/nickurt/laravel-pwned-passwords/license.svg)](LICENSE.md)

### Installation
Install this package with composer:
```
composer require nickurt/laravel-pwned-passwords
```
Copy the config files for the PwnedPasswords-plugin
```
php artisan vendor:publish --provider="nickurt\PwnedPasswords\ServiceProvider" --tag="config"
```

### Examples

#### Validation Rule - IsPwnedPassword
```php
// FormRequest ...

public function rules()
{
    return [
        'password' => ['required', new \nickurt\PwnedPasswords\Rules\IsPwnedPassword(20)]
    ];
}

// Manually ...

$validator = validator()->make(request()->all(), ['password' => ['required', new \nickurt\PwnedPasswords\Rules\IsPwnedPassword(20)]]);
```
The `IsPwnedPassword`-rule has one optional paramter `frequency` (default 10) to validate the request.
#### Manually Usage - IsPwnedPassword
```php
$isPwnedPassword = \PwnedPasswords::setFrequency(20)
    ->setPassword('laravel-pwned-passwords')
    ->isPwnedPassword();
```
#### Events
You can listen to the `IsPwnedPassword` event, e.g. if you want to log the `IsPwnedPassword`-requests in your application
##### IsPwnedPassword Event
This event will be fired when the password is above the frequency of pwned passwords
`nickurt\PwnedPasswords\Events\IsPwnedPassword`
### Tests
```sh
composer test
```
- - - 
