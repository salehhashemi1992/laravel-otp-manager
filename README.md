<div align="center">

# Laravel OTP Manager

[![Latest Version on Packagist](https://img.shields.io/packagist/v/salehhashemi/laravel-otp-manager.svg?style=flat-square)](https://packagist.org/packages/salehhashemi/laravel-otp-manager)
[![Total Downloads](https://img.shields.io/packagist/dt/salehhashemi/laravel-otp-manager.svg?style=flat-square)](https://packagist.org/packages/salehhashemi/laravel-otp-manager)
[![GitHub Actions](https://img.shields.io/github/actions/workflow/status/salehhashemi1992/laravel-otp-manager/run-tests.yml?branch=main&label=tests)](https://github.com/salehhashemi1992/laravel-otp-manager/actions/workflows/run-tests.yml)
[![GitHub Actions](https://img.shields.io/github/actions/workflow/status/salehhashemi1992/laravel-otp-manager/static-analysis.yml?branch=main&label=static-analysis)](https://github.com/salehhashemi1992/laravel-otp-manager/actions/workflows/static-analysis.yml)
[![codecov](https://codecov.io/gh/salehhashemi1992/laravel-otp-manager/graph/badge.svg?token=EJB78FT27M)](https://codecov.io/gh/salehhashemi1992/laravel-otp-manager)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat)](https://phpstan.org/)

![Header Image](./assets/header.png)

</div>

The `OtpManager` class is responsible for sending and verifying one-time passwords (OTPs). It provides a comprehensive set of methods to generate, send, verify, and manage OTPs. It also integrates with Laravel cache system to throttle OTP sending and provides a layer of security by tracking OTP requests.

## Features
*  **Main Features**
   *   Generate OTP codes
   *   Send OTPs via mobile numbers
   *   Resend OTPs with built-in throttling
   *   Verify OTP codes
   *   Track OTP requests
*  **Security**
   *   Rate limiting of OTP generation attempts (`OtpRateLimiter` middleware)
   *   Otp Invalidation after multiple failed verifications
   *   Automatic deletion of OTP codes after successful verification
*  **Configuration**
   *  Customize rate-limiting thresholds, max allowed attempts, and auto-delete
*  **Flexibility**
   *  Supports multiple OTP types using enums
   *  Customizable mobile number validation

## Requirements

- `PHP: ^8.1`
- `Laravel framework: ^9`

| Version | L9                 | L10                | L11                |
|---------|--------------------|--------------------|--------------------|
| 1.5     | :white_check_mark: | :white_check_mark: | :white_check_mark: |

## Installation
To install the package, you can run the following command:
```bash
composer require salehhashemi/laravel-otp-manager
```
## Usage

### Sending OTP
```php
use Salehhashemi\OtpManager\Facade\OtpManager;

$sentOtp = OtpManager::send("1234567890");
```
### Resending OTP
The `sendAndRetryCheck` method will throw a `ValidationException` if you try to resend the OTP before the waiting time expires.
```php
$sentOtp = OtpManager::sendAndRetryCheck("1234567890");
```
### Verifying OTP
```php
$isVerified = OtpManager::verify("1234567890", 123456, "uuid-string");
```
### Deleting Verification Code
```php
$isDeleted = OtpManager::deleteVerifyCode("1234567890");
```

## Handling and Listening to the `OtpPrepared` Event
The `OtpManager` package emits an `OtpPrepared` event whenever a new OTP is generated. You can listen to this event and execute custom logic, such as sending the OTP via SMS or email. 

Here's how to set up an event listener:

### Step 1: Register the Event and Listener
First, you need to register the `OtpPrepared` event and its corresponding listener. Open your `EventServiceProvider` file, usually located at `app/Providers/EventServiceProvider.php`, and add the event and listener to the $listen array.

```php
protected $listen = [
    \Salehhashemi\OtpManager\Events\OtpPrepared::class => [
        \App\Listeners\SendOtpNotification::class,
    ],
];
```

### Step 2: Create the Listener
If the listener does not exist, you can generate it using the following Artisan command:

```bash
php artisan make:listener SendOtpNotification
```

### Step 3: Implement the Listener
Now open the generated `SendOtpNotification` listener file, typically located at `app/Listeners/`. You'll see a handle method, where you can add your custom logic for sending the OTP.

Here's a sample implementation:
```php
use Salehhashemi\OtpManager\Events\OtpPrepared;

class SendOtpNotification
{
    public function handle(OtpPrepared $event)
    {
        $mobile = $event->mobile;
        $otpCode = $event->code;

        // Send the OTP code to the mobile number
        // You can use your preferred SMS service here.
    }
}
```

### Step 4: Test the Event Listener
Once you've set up the listener, generate a new OTP through the `OtpManager` package to make sure the `OtpPrepared` event is being caught and the corresponding listener logic is being executed.

That's it! You've successfully set up an event listener for the `OtpPrepared` event in the `OtpManager` package.

## Using Enums for OTP Types
You can take advantage of enums to define your OTP types. Enums provide a more expressive way to manage different categories of OTPs.

### How to Define an OTP Enum
```php
use Salehhashemi\OtpManager\Contracts\OtpTypeInterface;

enum MyOtpEnum: string implements OtpTypeInterface
{
    case SIGNUP = 'signup';
    case RESET_PASSWORD = 'reset_password';

    public function identifier(): string
    {
        return $this->value;
    }
}
```
### Usage
After defining your enum, you can use it just like any other OTP type:
```php
OtpManager::send('1234567890', MyOtpEnum::SIGNUP);
OtpManager::verify('1234567890', $otpCode, $trackingCode, MyOtpEnum::SIGNUP);
```

## Configuration
To publish the config file, run the following command:
```bash
php artisan vendor:publish --provider="Salehhashemi\OtpManager\OtpManagerServiceProvider" --tag="config"
```
To publish the language files, run:
```bash
php artisan vendor:publish --provider="Salehhashemi\OtpManager\OtpManagerServiceProvider" --tag="lang"
```
After publishing, make sure to clear the config cache to apply your changes:
```bash
php artisan config:clear
```
Then, you can adjust the waiting_time, code_min, and code_max in the `config/otp.php`

## Middleware Protection
The OtpManager package includes built-in middleware (OtpRateLimiter) to protect your application routes from excessive OTP requests. This helps prevent potential abuse.

### To apply the middleware:

**Register the middleware:** Add `\Salehhashemi\OtpManager\Middleware\OtpRateLimiter::class` to the `middlewareAliases` array in your `app\Http\Kernel.php` file.

**Assign the middleware to routes:** You can apply it to specific routes or route groups where you want to implement rate limiting.

Example:

```php
Route::middleware('otp-rate-limiter')->group(function () {
    // Routes that require OTP rate limiting go here
});
```

## Custom Mobile Number Validation
The package comes with a default mobile number validator, but you can easily use your own. 

Here's how you can do it:

1. Create a Custom Validator Class
First, create a class that implements `MobileValidatorInterface`. This interface expects you to define a validate method.
    ```php
    use Salehhashemi\OtpManager\Contracts\MobileValidatorInterface;
    
    class CustomMobileValidator implements MobileValidatorInterface
    {
        public function validate(string $mobile): void
        {
            // Your validation logic here
        }
    }
    ```
2. Update Configuration
Next, open your OTP configuration file and update the `mobile_validation_class` option to use your custom validator class:
    ```php
    'mobile_validation_class' => CustomMobileValidator::class,
    ```

### Exceptions
* `\InvalidArgumentException` will be thrown if the mobile number is empty.
* `\Exception` will be thrown for general exceptions, like OTP generation failures.
* `\Illuminate\Validation\ValidationException` will be thrown for throttle restrictions.
* `\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException` will be thrown for throttled requests.

## Docker Setup
This project uses Docker for local development and testing. Make sure you have Docker and Docker Compose installed on your system before proceeding.

### Build the Docker images
```bash
docker-compose build
```

### Start the services
```bash
docker-compose up -d
```
To access the PHP container, you can use:
```bash
docker-compose exec php bash
```

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](changelog.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](contributing.md) for details.

## Credits

- [Saleh Hashemi](https://github.com/salehhashemi1992)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](license.md) for more information.
