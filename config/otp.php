<?php

use Salehhashemi\OtpManager\Validators\DefaultMobileValidator;

return [

    /*
    |--------------------------------------------------------------------------
    | OTP Waiting Time
    |--------------------------------------------------------------------------
    |
    | This option defines the number of seconds a user has to wait before
    | being allowed to request a new OTP. Set it to a reasonable value
    | to prevent abuse.
    |
    */
    'waiting_time' => 120,

    /*
    |--------------------------------------------------------------------------
    | OTP Code Range
    |--------------------------------------------------------------------------
    |
    | These options define the minimum and maximum range for the generated
    | OTP codes. Adjust these values as per your security requirements.
    |
    */
    'code_min' => 111111,
    'code_max' => 999999,

    /*
    |--------------------------------------------------------------------------
    | Mobile Validation Class
    |--------------------------------------------------------------------------
    |
    | This option defines the class responsible for validating mobile numbers.
    | If you want to use your own validation logic, you can create your
    | own class and replace the class name here.
    |
    */
    'mobile_validation_class' => DefaultMobileValidator::class,
];
