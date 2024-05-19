<?php

namespace Salehhashemi\OtpManager\Tests;

use Salehhashemi\OtpManager\Dto\OtpDto;

class OtpDtoTest extends TestCase
{
    public function test_constructor_initializes_properties_correctly(): void
    {
        $code = 123456;
        $trackingCode = '550e8400-e29b-41d4-a716-446655440000';

        $otpDto = new OtpDto($code, $trackingCode);

        $this->assertSame($code, $otpDto->code);
        $this->assertSame($trackingCode, $otpDto->trackingCode);
    }

    public function test_toArray_returns_correct_array(): void
    {
        $code = 123456;
        $trackingCode = '550e8400-e29b-41d4-a716-446655440000';

        $otpDto = new OtpDto($code, $trackingCode);

        $expectedArray = [
            'code' => $code,
            'tracking_code' => $trackingCode,
        ];

        $this->assertSame($expectedArray, $otpDto->toArray());
    }
}
