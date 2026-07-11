<?php

namespace Modules\TitanZero\Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Modules\TitanZero\Services\TitanZeroRateLimitService;

/**
 * Unit tests for TitanZeroRateLimitService.
 * Uses the array cache driver so no Redis/memcached dependency.
 */
class RateLimitServiceTest extends TestCase
{
    private TitanZeroRateLimitService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->service = new TitanZeroRateLimitService();
    }

    public function test_first_call_is_allowed(): void
    {
        $result = $this->service->check(99);
        $this->assertTrue($result['allowed']);
        $this->assertNull($result['reason']);
    }

    public function test_increment_does_not_throw(): void
    {
        $this->expectNotToPerformAssertions();
        $this->service->increment(99, 100);
    }

    public function test_check_after_many_increments_still_allowed_below_limit(): void
    {
        // Increment 5 times — well below default 20 req/min
        for ($i = 0; $i < 5; $i++) {
            $this->service->increment(99);
        }
        $result = $this->service->check(99);
        $this->assertTrue($result['allowed']);
    }

    public function test_different_company_ids_have_independent_counters(): void
    {
        // Flood company 1
        for ($i = 0; $i < 20; $i++) {
            $this->service->increment(1);
        }

        // Company 2 should still be allowed
        $result = $this->service->check(2);
        $this->assertTrue($result['allowed']);
    }
}
