<?php

namespace Salehhashemi\OtpManager\Facade;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Facade;
use Salehhashemi\OtpManager\Contracts\OtpTypeInterface;
use Salehhashemi\OtpManager\Dto\OtpDto;
use Salehhashemi\OtpManager\Dto\SentOtpDto;

/**
 * @method static SentOtpDto send(string $mobile, OtpTypeInterface $type = null, array $params = [])
 * @method static SentOtpDto sendAndRetryCheck(string $mobile, OtpTypeInterface $type = null, array $params = [])
 * @method static bool verify(string $mobile, int $otp, string $trackingCode, OtpTypeInterface $type = null)
 * @method static OtpDto|null getVerifyCode(string $mobile, OtpTypeInterface $type = null)
 * @method static bool deleteVerifyCode(string $mobile, OtpTypeInterface $type = null)
 * @method static Carbon|null getSentAt(string $mobile, OtpTypeInterface $type = null)
 * @method static bool isVerifyCodeHasBeenSent(string $mobile, OtpTypeInterface $type = null)
 *
 * @see \Salehhashemi\OtpManager\OtpManager
 */
class OtpManager extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return \Salehhashemi\OtpManager\OtpManager::class;
    }
}
