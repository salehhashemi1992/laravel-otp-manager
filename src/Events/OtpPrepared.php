<?php

namespace Salehhashemi\OtpManager\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OtpPrepared
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $mobile;

    public string $code;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(string $mobile, string $code)
    {
        $this->mobile = $mobile;
        $this->code = $code;
    }
}
