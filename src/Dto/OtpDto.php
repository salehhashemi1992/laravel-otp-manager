<?php

declare(strict_types=1);

namespace Salehhashemi\OtpManager\Dto;

class OtpDto
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
     * Constructor.
     *
     * @param int $code Code
     * @param string $trackingCode
     */
    public function __construct(int $code, string $trackingCode)
    {
        $this->code = $code;
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
        ];
    }
}
