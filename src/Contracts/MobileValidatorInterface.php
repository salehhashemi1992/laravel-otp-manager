<?php

namespace Salehhashemi\OtpManager\Contracts;

interface MobileValidatorInterface
{
    /**
     * Validates the provided mobile number.
     *
     * @param  string  $mobile  The mobile number to validate.
     *
     * @throws \InvalidArgumentException If the mobile number is empty.
     */
    public function validate(string $mobile): void;
}
