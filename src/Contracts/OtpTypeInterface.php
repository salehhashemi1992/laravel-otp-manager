<?php

declare(strict_types=1);

namespace Salehhashemi\OtpManager\Contracts;

interface OtpTypeInterface
{
    /**
     * This method should return a unique identifier for the OTP type.
     */
    public function identifier(): string;
}
