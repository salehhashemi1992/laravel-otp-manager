<?php

declare(strict_types=1);

namespace Salehhashemi\OtpManager\Dto;

class OtpDto
{
    public int $code;

    public string $trackingCode;

    public function __construct(int $code, string $trackingCode)
    {
        $this->code = $code;
        $this->trackingCode = $trackingCode;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'tracking_code' => $this->trackingCode,
        ];
    }
}
