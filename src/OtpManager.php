<?php

declare(strict_types=1);

namespace Salehhashemi\OtpManager;

use App\Events\OtpProcessed;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Salehhashemi\ConfigurableCache\ConfigurableCache;
use Salehhashemi\OtpManager\Dto\OtpDto;
use Salehhashemi\OtpManager\Dto\SentOtpDto;

/* A class that is responsible for sending and verifying one time passwords. */
class OtpManager
{
    private string $trackingCode;

    private string $type;

    /**
     * @var int
     */
    private int $waitingTime;

    public function __construct()
    {
        $this->waitingTime = config('otp.waiting_time');
    }

    /**
     * Send a new OTP.
     *
     * @param string $mobile Mobile number
     * @param string $type
     * @return \Salehhashemi\OtpManager\Dto\SentOtpDto
     *
     * @throws \Exception
     */
    public function send(string $mobile, string $type): SentOtpDto
    {
        $this->type = $type;
        $this->trackingCode = Str::uuid()->toString();

        $otp = new SentOtpDto($this->getNewCode($mobile), $this->waitingTime, $this->trackingCode);

        event(new OtpProcessed(mobile: $mobile, code: (string) $otp->code));

        return $otp;
    }

    /**
     * Resend OTP based on waiting time.
     *
     * @param string $mobile The mobile number to send the OTP to.
     * @param string $type
     * @return \Salehhashemi\OtpManager\Dto\SentOtpDto Sent Otp Dto object
     *
     * @throws \Exception
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

        throw ValidationException::withMessages([
            'otp' => [
                trans('auth.throttle', ['seconds' => $this->waitingTime]),
            ],
        ]);
    }

    /**
     * Verify the OTP code.
     *
     * @param string $mobile Mobile number
     * @param string $type
     * @param int $otp Otp
     * @param string|null $trackingCode
     * @return bool
     */
    public function verify(string $mobile, string $type, int $otp, ?string $trackingCode): bool
    {
        $this->type = $type;
        $this->trackingCode = $trackingCode;

        $otpDto = $this->getVerifyCode($mobile, $type);

        return $otpDto && $otp === $otpDto->code && $trackingCode === $otpDto->trackingCode;
    }

    /**
     * Fetch the OTP code for verification.
     *
     * @param string $mobile Mobile number
     * @param string $type
     * @return OtpDto|null
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
     * @param string $mobile Mobile number
     * @param string $type
     * @return bool
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
     * It returns the time when the OTP was sent to the user
     *
     * @param  string  $mobile The mobile number to send the OTP to.
     * @return \Illuminate\Support\Carbon|null A Carbon instance of the time the OTP was sent.
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
     * > It checks if the cache key exists in the cache store
     *
     * @param  string  $mobile The mobile number to send the OTP to.
     * @return bool The function isVerifyCodeHasBeenSent() is returning a boolean value.
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
     * @param  string  $mobile Mobile number
     * @return int
     *
     * @throws \Exception
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
     * @param  string  $mobile Mobile number
     * @param  string  $for Cache key for
     * @return string
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
