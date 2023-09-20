<?php

namespace Salehhashemi\OtpManager\Tests;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Salehhashemi\ConfigurableCache\ConfigurableCacheServiceProvider;
use Salehhashemi\OtpManager\Events\OtpPrepared;
use Salehhashemi\OtpManager\OtpManager;
use Salehhashemi\OtpManager\OtpManagerServiceProvider;

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
        $sentOtp = $otpManager->send('1234567890', 'sms');

        $this->assertNotNull($sentOtp);

        Event::assertDispatched(OtpPrepared::class, function ($event) use ($sentOtp) {
            return $event->code === (string) $sentOtp->code;
        });
    }

    public function test_verify_function_verifies_otp()
    {
        $otpManager = new OtpManager();
        $sentOtp = $otpManager->send('1234567890', 'sms');

        $isVerified = $otpManager->verify('1234567890', 'sms', $sentOtp->code, $sentOtp->trackingCode);

        $this->assertTrue($isVerified);
    }

    public function test_delete_function_deletes_otp()
    {
        $otpManager = new OtpManager();
        $otpManager->send('1234567890', 'sms');

        $otpManager->deleteVerifyCode('1234567890', 'sms');

        $isVerified = $otpManager->verify('1234567890', 'sms', 123456, '123456');

        $this->assertFalse($isVerified);
    }

    public function test_send_function_fails_for_empty_mobile()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Mobile number cannot be empty.');

        $otpManager = new OtpManager();
        $otpManager->send('', 'sms');
    }

    public function test_verify_function_fails_for_wrong_code()
    {
        $otpManager = new OtpManager();
        $sentOtp = $otpManager->send('1234567890', 'sms');

        $isVerified = $otpManager->verify('1234567890', 'sms', 1, $sentOtp->trackingCode);

        $this->assertFalse($isVerified);
    }

    public function test_verify_function_fails_for_wrong_tracking_code()
    {
        $otpManager = new OtpManager();
        $sentOtp = $otpManager->send('1234567890', 'sms');

        $isVerified = $otpManager->verify('1234567890', 'sms', $sentOtp->code, 'wrongTrackingCode');

        $this->assertFalse($isVerified);
    }

    public function test_getSentAt_returns_correct_time()
    {
        $otpManager = new OtpManager();
        $otpManager->send('1234567890', 'sms');

        $sentAt = $otpManager->getSentAt('1234567890', 'sms');

        $this->assertInstanceOf(Carbon::class, $sentAt);
    }

    public function test_isVerifyCodeHasBeenSent_returns_true()
    {
        $otpManager = new OtpManager();
        $otpManager->send('1234567890', 'sms');

        $isSent = $otpManager->isVerifyCodeHasBeenSent('1234567890', 'sms');

        $this->assertTrue($isSent);
    }

    public function test_sendAndRetryCheck_throws_validation_exception_for_quick_retry()
    {
        $otpManager = new OtpManager();
        $otpManager->send('1234567890', 'sms');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Too many OTP attempts. Please try again in');

        $otpManager->sendAndRetryCheck('1234567890', 'sms');
    }
}
