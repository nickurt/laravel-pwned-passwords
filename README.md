## Laravel Pwned Passwords

[![Latest Stable Version](https://poser.pugx.org/nickurt/laravel-pwned-passwords/v/stable?format=flat-square)](https://packagist.org/packages/nickurt/laravel-pwned-passwords)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/nickurt/laravel-pwned-passwords/master.svg?style=flat-square)](https://travis-ci.org/nickurt/laravel-pwned-passwords)
[![Total Downloads](https://img.shields.io/packagist/dt/nickurt/laravel-pwned-passwords.svg?style=flat-square)](https://packagist.org/packages/nickurt/laravel-pwned-passwords)

### Installation
Install this package with composer:
```
composer require nickurt/laravel-pwned-passwords
```

Add the provider to config/app.php file

```php
'nickurt\PwnedPasswords\ServiceProvider',
```

and the facade in the file

```php
'PwnedPasswords' => 'nickurt\PwnedPasswords\Facade',
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