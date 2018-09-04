## Laravel Pwned Passwords

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
$validator = validator()->make(request()->all(), ['password' => [new \nickurt\PwnedPasswords\Rules\IsPwnedPassword(
    request()->input('password'), 100
)]]);
```
The `IsPwnedPasswords` requires a `password` and an optional `frequency` parameter to validate the request.
#### Manually Usage - IsPwnedPassword
```php
$isPwnedPassword = (new \nickurt\PwnedPasswords\PwnedPasswords())
	->setPassword('laravel-pwned-passwords')
	->isPwnedPassword();
	
// ...	
$isPwnedPassword = pwnedpassword()
    ->setPassword('laravel-pwned-password')
    ->isPwnedPassword();
```
### Tests
```sh
phpunit
```
- - - 