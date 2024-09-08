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

class OtpManagerTest extends TestCase
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

        $otpManager = new OtpManager;
        $sentOtp = $otpManager->send('1234567890', MyOtpEnum::SIGNUP);

        $this->assertNotNull($sentOtp);

        Event::assertDispatched(OtpPrepared::class, function ($event) use ($sentOtp) {
            return $event->code === (string) $sentOtp->code;
        });
    }

    public function test_verify_function_verifies_otp()
    {
        $otpManager = new OtpManager;
        $sentOtp = $otpManager->send('1234567890', MyOtpEnum::SIGNUP);

        $isVerified = $otpManager->verify('1234567890', $sentOtp->code, $sentOtp->trackingCode, MyOtpEnum::SIGNUP);

        $this->assertTrue($isVerified);
    }

    public function test_verify_function_verifies_otp_without_type()
    {
        $otpManager = new OtpManager;
        $sentOtp = $otpManager->send('1234567890');

        $isVerified = $otpManager->verify('1234567890', $sentOtp->code, $sentOtp->trackingCode);

        $this->assertTrue($isVerified);
    }

    public function test_delete_function_deletes_otp()
    {
        $otpManager = new OtpManager;
        $otpManager->send('1234567890', MyOtpEnum::SIGNUP);

        $otpManager->deleteVerifyCode('1234567890', MyOtpEnum::SIGNUP);

        $isVerified = $otpManager->verify('1234567890', 123456, '123456', MyOtpEnum::SIGNUP);

        $this->assertFalse($isVerified);
    }

    public function test_send_function_fails_for_empty_mobile()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Mobile number cannot be empty.');

        $otpManager = new OtpManager;
        $otpManager->send('', MyOtpEnum::SIGNUP);
    }

    public function test_verify_function_fails_for_wrong_code()
    {
        $otpManager = new OtpManager;
        $sentOtp = $otpManager->send('1234567890', MyOtpEnum::SIGNUP);

        $isVerified = $otpManager->verify('1234567890', 1, $sentOtp->trackingCode, MyOtpEnum::SIGNUP);

        $this->assertFalse($isVerified);
    }

    public function test_verify_function_fails_for_wrong_tracking_code()
    {
        $otpManager = new OtpManager;
        $sentOtp = $otpManager->send('1234567890', MyOtpEnum::SIGNUP);

        $isVerified = $otpManager->verify('1234567890', $sentOtp->code, 'wrongTrackingCode', MyOtpEnum::SIGNUP);

        $this->assertFalse($isVerified);
    }

    public function test_getSentAt_returns_correct_time()
    {
        $otpManager = new OtpManager;
        $otpManager->send('1234567890', MyOtpEnum::SIGNUP);

        $sentAt = $otpManager->getSentAt('1234567890', MyOtpEnum::SIGNUP);

        $this->assertInstanceOf(Carbon::class, $sentAt);
    }

    public function test_isVerifyCodeHasBeenSent_returns_true()
    {
        $otpManager = new OtpManager;
        $otpManager->send('1234567890', MyOtpEnum::SIGNUP);

        $isSent = $otpManager->isVerifyCodeHasBeenSent('1234567890', MyOtpEnum::SIGNUP);

        $this->assertTrue($isSent);
    }

    public function test_sendAndRetryCheck_throws_validation_exception_for_quick_retry()
    {
        $otpManager = new OtpManager;
        $otpManager->send('1234567890', MyOtpEnum::SIGNUP);

        $this->expectException(ValidationException::class);

        $otpManager->sendAndRetryCheck('1234567890', MyOtpEnum::SIGNUP);
    }

    public function test_verify_function_fails_after_exceeding_max_attempts_and_advises_new_otp_request()
    {
        $otpManager = new OtpManager;

        // Adjust the configuration for maximum verification attempts to 1 for the test.
        config(['otp.max_verify_attempts' => 1]);

        // Send OTP
        $otpManager->send('1234567890', MyOtpEnum::SIGNUP);

        // First failed attempt
        $otpManager->verify('1234567890', 0, 'incorrect', MyOtpEnum::SIGNUP);

        // Second attempt should fail and advise for new OTP request
        try {
            $otpManager->verify('1234567890', 0, 'incorrectAgain', MyOtpEnum::SIGNUP);
            $this->fail('Expected ValidationException for exceeding max verification attempts was not thrown.');
        } catch (ValidationException $e) {
            $this->assertSame(__('otp-manager::otp.request_new'), $e->getMessage());
        }

        // Ensure that a new OTP can be requested immediately after exceeding attempts
        $sentOtp = $otpManager->send('1234567890', MyOtpEnum::SIGNUP);
        $this->assertNotNull($sentOtp, 'Failed to send a new OTP after exceeding maximum verification attempts.');

        try {
            $otpManager->sendAndRetryCheck('1234567890', MyOtpEnum::SIGNUP);
            $this->fail('Expected ValidationException for throttle.');
        } catch (ValidationException) {
            // true
        }
    }

    public function test_attempts_reset_after_successful_verification()
    {
        $otpManager = new OtpManager;
        config(['otp.max_verify_attempts' => 2]);

        // Send and verify OTP successfully
        $sentOtp = $otpManager->send('1234567890', MyOtpEnum::SIGNUP);
        $isVerified = $otpManager->verify('1234567890', $sentOtp->code, $sentOtp->trackingCode, MyOtpEnum::SIGNUP);

        $this->assertTrue($isVerified);

        // Ensure that after successful verification, we can attempt to verify again without instant failure
        $sentOtp = $otpManager->send('1234567890', MyOtpEnum::SIGNUP);
        $isVerifiedAgain = $otpManager->verify('1234567890', $sentOtp->code, 'incorrectCode', MyOtpEnum::SIGNUP);

        $this->assertFalse($isVerifiedAgain);
    }
}
