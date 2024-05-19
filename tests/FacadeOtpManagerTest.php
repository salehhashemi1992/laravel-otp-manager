<?php

namespace Salehhashemi\OtpManager\Tests;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Salehhashemi\ConfigurableCache\ConfigurableCacheServiceProvider;
use Salehhashemi\OtpManager\Events\OtpPrepared;
use Salehhashemi\OtpManager\Facade\OtpManager;
use Salehhashemi\OtpManager\OtpManagerServiceProvider;
use Salehhashemi\OtpManager\Tests\Enums\MyOtpEnum;

class FacadeOtpManagerTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getPackageProviders($app): array
    {
        return [OtpManagerServiceProvider::class, ConfigurableCacheServiceProvider::class];
    }

    public function test_send_function_sends_otp()
    {
        Event::fake();

        $sentOtp = OtpManager::send('1234567890', MyOtpEnum::SIGNUP);

        $this->assertNotNull($sentOtp);

        Event::assertDispatched(OtpPrepared::class, function ($event) use ($sentOtp) {
            return $event->code === (string) $sentOtp->code;
        });
    }

    public function test_verify_function_verifies_otp()
    {
        $sentOtp = OtpManager::send('1234567890', MyOtpEnum::SIGNUP);

        $isVerified = OtpManager::verify('1234567890', $sentOtp->code, $sentOtp->trackingCode, MyOtpEnum::SIGNUP);

        $this->assertTrue($isVerified);
    }

    public function test_verify_function_verifies_otp_without_type()
    {
        $sentOtp = OtpManager::send('1234567890');

        $isVerified = OtpManager::verify('1234567890', $sentOtp->code, $sentOtp->trackingCode);

        $this->assertTrue($isVerified);
    }

    public function test_delete_function_deletes_otp()
    {
        OtpManager::send('1234567890', MyOtpEnum::SIGNUP);

        OtpManager::deleteVerifyCode('1234567890', MyOtpEnum::SIGNUP);

        $isVerified = OtpManager::verify('1234567890', 123456, '123456', MyOtpEnum::SIGNUP);

        $this->assertFalse($isVerified);
    }

    public function test_send_function_fails_for_empty_mobile()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Mobile number cannot be empty.');

        OtpManager::send('', MyOtpEnum::SIGNUP);
    }

    public function test_verify_function_fails_for_wrong_code()
    {
        $sentOtp = OtpManager::send('1234567890', MyOtpEnum::SIGNUP);

        $isVerified = OtpManager::verify('1234567890', 1, $sentOtp->trackingCode, MyOtpEnum::SIGNUP);

        $this->assertFalse($isVerified);
    }

    public function test_verify_function_fails_for_wrong_tracking_code()
    {
        $sentOtp = OtpManager::send('1234567890', MyOtpEnum::SIGNUP);

        $isVerified = OtpManager::verify('1234567890', $sentOtp->code, 'wrongTrackingCode', MyOtpEnum::SIGNUP);

        $this->assertFalse($isVerified);
    }

    public function test_getSentAt_returns_correct_time()
    {
        OtpManager::send('1234567890', MyOtpEnum::SIGNUP);

        $sentAt = OtpManager::getSentAt('1234567890', MyOtpEnum::SIGNUP);

        $this->assertInstanceOf(Carbon::class, $sentAt);
    }

    public function test_isVerifyCodeHasBeenSent_returns_true()
    {
        OtpManager::send('1234567890', MyOtpEnum::SIGNUP);

        $isSent = OtpManager::isVerifyCodeHasBeenSent('1234567890', MyOtpEnum::SIGNUP);

        $this->assertTrue($isSent);
    }

    public function test_sendAndRetryCheck_throws_validation_exception_for_quick_retry()
    {
        OtpManager::send('1234567890', MyOtpEnum::SIGNUP);

        $this->expectException(ValidationException::class);

        OtpManager::sendAndRetryCheck('1234567890', MyOtpEnum::SIGNUP);
    }
}
