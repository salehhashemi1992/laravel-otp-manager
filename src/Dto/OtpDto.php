<?php

declare(strict_types=1);

namespace Salehhashemi\OtpManager\Dto;

class OtpDto
{
    public function __construct(
        public int $code,
        public string $trackingCode
    ) {}

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
