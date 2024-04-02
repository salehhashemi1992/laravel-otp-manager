<?php

namespace Salehhashemi\OtpManager\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class OtpRateLimiter
{
    public function handle(Request $request, Closure $next, ?string $key = null): mixed
    {
        if ($key === null) {
            $key = 'otp-rate-limit:'.$request->ip().'|'.Str::slug($request->userAgent() ?? 'default', '|');
        }

        $maxAttempts = config('otp.rate_limiting.max_attempts', 5);
        $decaySeconds = config('otp.rate_limiting.decay_minutes', 1) * 60;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            throw new TooManyRequestsHttpException(
                $seconds,
                "Too Many Attempts. Please try again in $seconds seconds."
            );
        }

        RateLimiter::hit($key, $decaySeconds);

        return $next($request);
    }
}
