<?php

declare(strict_types=1);

namespace Salehhashemi\OtpManager;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Salehhashemi\ConfigurableCache\ConfigurableCache;
use Salehhashemi\OtpManager\Dto\OtpDto;
use Salehhashemi\OtpManager\Dto\SentOtpDto;
use Salehhashemi\OtpManager\Events\OtpPrepared;

/* A class that is responsible for sending and verifying one time passwords. */
class OtpManager
{
    private string $trackingCode;

    private string $type;

    private int $waitingTime;

    public function __construct()
    {
        $this->waitingTime = config('otp.waiting_time');
    }

    /**
     * Send a new OTP.
     *
     * Generates a new OTP code, triggers an event, and returns the sent OTP details.
     *
     * @param  string  $mobile  The mobile number to which the OTP should be sent.
     * @param  string  $type    The type or category of OTP being sent (e.g., 'login', 'reset_password').
     * @return \Salehhashemi\OtpManager\Dto\SentOtpDto  An object containing details of the sent OTP.
     *
     * @throws \Exception  If the OTP generation fails or any other exception occurs.
     */
    public function send(string $mobile, string $type): SentOtpDto
    {
        $this->type = $type;
        $this->trackingCode = Str::uuid()->toString();

        $otp = new SentOtpDto($this->getNewCode($mobile), $this->waitingTime, $this->trackingCode);

        event(new OtpPrepared(mobile: $mobile, code: (string) $otp->code));

        return $otp;
    }

    /**
     * Resend OTP based on waiting time.
     *
     * Checks if the waiting time has passed since the last OTP was sent.
     * If so, resends the OTP to the given mobile number; otherwise, throws a ValidationException.
     *
     * @param  string  $mobile  The mobile number to which the OTP should be resent.
     * @param  string  $type    The type or category of OTP being sent (e.g., 'login', 'reset_password').
     * @return \Salehhashemi\OtpManager\Dto\SentOtpDto  An object containing details of the sent OTP.
     *
     * @throws \Illuminate\Validation\ValidationException  If the OTP cannot be resent due to throttle restrictions.
     * @throws \Exception  If any other exception occurs.
     */
    public function sendAndRetryCheck(string $mobile, string $type): SentOtpDto
    {
        $this->type = $type;

        $created = $this->getSentAt($mobile, $type);
        if (! $created) {
            return $this->send($mobile, $type);
        }

        $retryAfter = $created->addSeconds($this->waitingTime);
        if (Carbon::now()->greaterThan($retryAfter)) {
            return $this->send($mobile, $type);
        }

        $remainingTime = $retryAfter->diffInSeconds(Carbon::now());

        throw ValidationException::withMessages([
            'otp' => [
                trans('OtpManager::otp.throttle', ['seconds' => $remainingTime]),
            ],
        ]);
    }

    /**
     * Verify the OTP code.
     *
     * Compares the provided OTP code and tracking code with the stored ones
     * for the given mobile number and OTP type. Returns true if they match.
     *
     * @param  string  $mobile The mobile number associated with the OTP.
     * @param  string  $type The type or category of OTP (e.g., 'login', 'reset_password').
     * @param  int  $otp The OTP code to verify.
     * @param  string  $trackingCode The tracking code associated with the OTP.
     * @return bool  True if the provided OTP and tracking code match the stored ones, false otherwise.
     */
    public function verify(string $mobile, string $type, int $otp, string $trackingCode): bool
    {
        $this->type = $type;
        $this->trackingCode = $trackingCode;

        $otpDto = $this->getVerifyCode($mobile, $type);

        return $otpDto && $otp === $otpDto->code && $trackingCode === $otpDto->trackingCode;
    }

    /**
     * Fetch the OTP code for verification.
     *
     * Retrieves the OTP code associated with the given mobile number and OTP type from the cache.
     * Returns null if the mobile number is empty or if no OTP code is found.
     *
     * @param  string  $mobile  The mobile number associated with the OTP.
     * @param  string  $type    The type or category of OTP (e.g., 'login', 'reset_password').
     * @return \Salehhashemi\OtpManager\Dto\OtpDto|null  An OtpDto object containing the OTP code and tracking code, or null if not found.
     */
    public function getVerifyCode(string $mobile, string $type): ?OtpDto
    {
        $this->type = $type;

        if (empty($mobile)) {
            return null;
        }

        return ConfigurableCache::get($this->getCacheKey($mobile, 'value'), 'otp');
    }

    /**
     * Delete the verification code for a mobile number.
     */
    public function deleteVerifyCode(string $mobile, string $type): bool
    {
        $this->type = $type;

        if (empty($mobile)) {
            return false;
        }

        return ConfigurableCache::delete($this->getCacheKey($mobile, 'value'), 'otp');
    }

    /**
     * Retrieve the time when the OTP was sent to the user.
     *
     * @return \Illuminate\Support\Carbon|null A Carbon instance representing the time the OTP was sent, or null if not available.
     */
    public function getSentAt(string $mobile, string $type): ?Carbon
    {
        $this->type = $type;

        if (empty($mobile)) {
            return null;
        }

        $created = ConfigurableCache::get($this->getCacheKey($mobile, 'created'), 'otp');
        if (! $created) {
            return null;
        }

        return Carbon::createFromTimestamp($created);
    }

    /**
     * Check if a verification code has been sent to a specified mobile number.
     *
     * @return bool    True if the verification code has been sent, false otherwise.
     */
    public function isVerifyCodeHasBeenSent(string $mobile, string $type): bool
    {
        $this->type = $type;

        if (empty($mobile)) {
            return false;
        }

        return ConfigurableCache::get($this->getCacheKey($mobile, 'value'), 'otp')['otp'] !== null;
    }

    /**
     * Generate a new OTP code within the configured range and store it in the cache.
     *
     * @throws \Exception  If random number generation fails.
     */
    protected function getNewCode(string $mobile): int
    {
        $min = config('otp.code_min');
        $max = config('otp.code_max');

        $otp = random_int($min, $max);

        $otpDto = new OtpDto($otp, $this->trackingCode);

        ConfigurableCache::put($this->getCacheKey($mobile, 'value'), $otpDto, 'otp');
        ConfigurableCache::put($this->getCacheKey($mobile, 'created'), time(), 'otp');

        return $otp;
    }

    /**
     * Generate a cache key for storing or retrieving OTP-related information.
     *
     * This function constructs a cache key by combining the mobile number,
     * the intended usage ('for'), and the OTP type. The generated key is
     * used for caching OTP values and associated information.
     *
     * @param  string  $mobile  The mobile number to which the OTP will be sent.
     * @param  string  $for     Indicates the intended usage of the cache key (e.g., 'value', 'created').
     * @return string  The generated cache key.
     */
    protected function getCacheKey(string $mobile, string $for): string
    {
        return sprintf(
            'for_%s_%s_%s',
            $mobile,
            $for,
            $this->type
        );
    }
}
