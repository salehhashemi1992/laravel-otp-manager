<?php

declare(strict_types=1);

namespace Salehhashemi\OtpManager\Dto;

class SentOtpDto
{
    public int $code;

    public string $trackingCode;

    private int $waitingTime;

    public function __construct(int $code, int $waitingTime, string $trackingCode)
    {
        $this->code = $code;
        $this->waitingTime = $waitingTime;
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
            'waiting_time' => $this->waitingTime,
        ];
    }
}
