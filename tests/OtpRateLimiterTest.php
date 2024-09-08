<?php

namespace Salehhashemi\OtpManager\Tests;

use Illuminate\Http\Request;
use Salehhashemi\ConfigurableCache\ConfigurableCacheServiceProvider;
use Salehhashemi\OtpManager\Middleware\OtpRateLimiter;
use Salehhashemi\OtpManager\OtpManagerServiceProvider;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class OtpRateLimiterTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getPackageProviders($app): array
    {
        return [OtpManagerServiceProvider::class, ConfigurableCacheServiceProvider::class];
    }

    public function test_middleware_blocks_requests_above_limit()
    {
        config(['otp.rate_limiting.max_attempts' => 3]);

        $request = Request::create('/', 'GET', ['REMOTE_ADDR' => '127.0.0.1']);

        $middleware = new OtpRateLimiter;

        $this->expectException(TooManyRequestsHttpException::class);

        // Trigger middleware multiple times to exceed the limit
        for ($i = 0; $i < 5; $i++) {
            $response = $middleware->handle($request, function () {});
        }
    }

    public function test_middleware_does_not_block_requests_below_limit()
    {
        config(['otp.rate_limiting.max_attempts' => 3]);

        $request = Request::create('/', 'GET', ['REMOTE_ADDR' => '127.0.0.1']);

        $middleware = new OtpRateLimiter;

        for ($i = 0; $i < 2; $i++) {
            $response = $middleware->handle($request, function () {
                return response('Passed', 200);
            });
        }

        // Assert that the last response has the correct status code
        $this->assertEquals(200, $response->status());
    }
}
