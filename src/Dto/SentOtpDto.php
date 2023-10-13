<?php

declare(strict_types=1);

namespace Salehhashemi\OtpManager\Dto;

class SentOtpDto
{
    public function __construct(
        public int $code,
        private int $waitingTime,
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
            'waiting_time' => $this->waitingTime,
        ];
    }
}
