<?php

declare(strict_types=1);

namespace Salehhashemi\OtpManager\Dto;

class SentOtpDto
{
    /**
     * @var int
     */
    public int $code;

    /**
     * @var string
     */
    public string $trackingCode;

    /**
     * @var int
     */
    private int $waitingTime;

    /**
     * Constructor.
     *
     * @param int $code Code
     * @param int $waitingTime
     * @param string $trackingCode
     */
    public function __construct(int $code, int $waitingTime, string $trackingCode)
    {
        $this->code = $code;
        $this->waitingTime = $waitingTime;
        $this->trackingCode = $trackingCode;
    }

    /**
     * @return array
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
