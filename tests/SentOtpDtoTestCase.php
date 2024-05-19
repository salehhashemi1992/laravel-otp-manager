<?php

namespace Salehhashemi\OtpManager\Tests;

use Salehhashemi\OtpManager\Dto\SentOtpDto;

class SentOtpDtoTestCase extends TestCase
{
    public function test_constructor_initializes_properties_correctly(): void
    {
        $code = 123456;
        $waitingTime = 60;
        $trackingCode = '550e8400-e29b-41d4-a716-446655440000';

        $sentOtpDto = new SentOtpDto($code, $waitingTime, $trackingCode);

        $reflectionClass = new \ReflectionClass(SentOtpDto::class);

        $codeProperty = $reflectionClass->getProperty('code');
        $codeProperty->setAccessible(true);

        $waitingTimeProperty = $reflectionClass->getProperty('waitingTime');
        $waitingTimeProperty->setAccessible(true);

        $trackingCodeProperty = $reflectionClass->getProperty('trackingCode');
        $trackingCodeProperty->setAccessible(true);

        $this->assertSame($code, $codeProperty->getValue($sentOtpDto));
        $this->assertSame($waitingTime, $waitingTimeProperty->getValue($sentOtpDto));
        $this->assertSame($trackingCode, $trackingCodeProperty->getValue($sentOtpDto));
    }

    public function test_toArray_returns_correct_array(): void
    {
        $code = 123456;
        $waitingTime = 60;
        $trackingCode = '550e8400-e29b-41d4-a716-446655440000';

        $sentOtpDto = new SentOtpDto($code, $waitingTime, $trackingCode);

        $expectedArray = [
            'code' => $code,
            'tracking_code' => $trackingCode,
            'waiting_time' => $waitingTime,
        ];

        $this->assertSame($expectedArray, $sentOtpDto->toArray());
    }
}
