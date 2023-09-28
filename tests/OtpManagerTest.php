<?php

namespace Salehhashemi\OtpManager\Tests;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Salehhashemi\ConfigurableCache\ConfigurableCacheServiceProvider;
use Salehhashemi\OtpManager\Events\OtpPrepared;
use Salehhashemi\OtpManager\OtpManager;
use Salehhashemi\OtpManager\OtpManagerServiceProvider;
use Salehhashemi\OtpManager\Tests\Enums\MyOtpEnum;

class OtpManagerTest extends BaseTest
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

        $otpManager = new OtpManager();
        $sentOtp = $otpManager->send('1234567890', MyOtpEnum::SIGNUP);

        $this->assertNotNull($sentOtp);

        Event::assertDispatched(OtpPrepared::class, function ($event) use ($sentOtp) {
            return $event->code === (string) $sentOtp->code;
        });
    }

    public function test_verify_function_verifies_otp()
    {
        $otpManager = new OtpManager();
        $sentOtp = $otpManager->send('1234567890', MyOtpEnum::SIGNUP);

        $isVerified = $otpManager->verify('1234567890', $sentOtp->code, $sentOtp->trackingCode, MyOtpEnum::SIGNUP);

        $this->assertTrue($isVerified);
    }

    public function test_verify_function_verifies_otp_without_type()
    {
        $otpManager = new OtpManager();
        $sentOtp = $otpManager->send('1234567890');

        $isVerified = $otpManager->verify('1234567890', $sentOtp->code, $sentOtp->trackingCode);

        $this->assertTrue($isVerified);
    }

    public function test_delete_function_deletes_otp()
    {
        $otpManager = new OtpManager();
        $otpManager->send('1234567890', MyOtpEnum::SIGNUP);

        $otpManager->deleteVerifyCode('1234567890', MyOtpEnum::SIGNUP);

        $isVerified = $otpManager->verify('1234567890', 123456, '123456', MyOtpEnum::SIGNUP);

        $this->assertFalse($isVerified);
    }

    public function test_send_function_fails_for_empty_mobile()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Mobile number cannot be empty.');

        $otpManager = new OtpManager();
        $otpManager->send('', MyOtpEnum::SIGNUP);
    }

    public function test_verify_function_fails_for_wrong_code()
    {
        $otpManager = new OtpManager();
        $sentOtp = $otpManager->send('1234567890', MyOtpEnum::SIGNUP);

        $isVerified = $otpManager->verify('1234567890', 1, $sentOtp->trackingCode, MyOtpEnum::SIGNUP);

        $this->assertFalse($isVerified);
    }

    public function test_verify_function_fails_for_wrong_tracking_code()
    {
        $otpManager = new OtpManager();
        $sentOtp = $otpManager->send('1234567890', MyOtpEnum::SIGNUP);

        $isVerified = $otpManager->verify('1234567890', $sentOtp->code, 'wrongTrackingCode', MyOtpEnum::SIGNUP);

        $this->assertFalse($isVerified);
    }

    public function test_getSentAt_returns_correct_time()
    {
        $otpManager = new OtpManager();
        $otpManager->send('1234567890', MyOtpEnum::SIGNUP);

        $sentAt = $otpManager->getSentAt('1234567890', MyOtpEnum::SIGNUP);

        $this->assertInstanceOf(Carbon::class, $sentAt);
    }

    public function test_isVerifyCodeHasBeenSent_returns_true()
    {
        $otpManager = new OtpManager();
        $otpManager->send('1234567890', MyOtpEnum::SIGNUP);

        $isSent = $otpManager->isVerifyCodeHasBeenSent('1234567890', MyOtpEnum::SIGNUP);

        $this->assertTrue($isSent);
    }

    public function test_sendAndRetryCheck_throws_validation_exception_for_quick_retry()
    {
        $otpManager = new OtpManager();
        $otpManager->send('1234567890', MyOtpEnum::SIGNUP);

        $this->expectException(ValidationException::class);

        $otpManager->sendAndRetryCheck('1234567890', MyOtpEnum::SIGNUP);
    }
}
