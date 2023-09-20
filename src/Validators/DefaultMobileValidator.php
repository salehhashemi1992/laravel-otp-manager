<?php

namespace Salehhashemi\OtpManager\Validators;

use Salehhashemi\OtpManager\Contracts\MobileValidatorInterface;

class DefaultMobileValidator implements MobileValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate(string $mobile): void
    {
        if (empty($mobile)) {
            throw new \InvalidArgumentException('Mobile number cannot be empty.');
        }
    }
}
