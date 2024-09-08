<?php

namespace Salehhashemi\OtpManager\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Salehhashemi\OtpManager\Contracts\OtpTypeInterface;

class OtpPrepared
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  array<string, string>  $params
     */
    public function __construct(
        public readonly string $mobile,
        public readonly string $code,
        public readonly ?OtpTypeInterface $type,
        public readonly array $params,
    ) {
    }
}
