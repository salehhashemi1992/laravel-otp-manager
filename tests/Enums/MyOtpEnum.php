<?php

declare(strict_types=1);

namespace Salehhashemi\OtpManager\Tests\Enums;

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
