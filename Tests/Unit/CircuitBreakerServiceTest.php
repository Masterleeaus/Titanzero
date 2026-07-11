<?php

namespace Modules\TitanZero\Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Modules\TitanZero\Services\CircuitBreaker\CircuitBreakerService;

/**
 * Unit tests for CircuitBreakerService.
 */
class CircuitBreakerServiceTest extends TestCase
{
    private CircuitBreakerService $breaker;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->breaker = new CircuitBreakerService();
        $this->breaker->reset(1, 'test_tool');
    }

    public function test_circuit_is_closed_initially(): void
    {
        $this->assertFalse($this->breaker->isOpen(1, 'test_tool'));
    }

    public function test_below_min_calls_does_not_trip(): void
    {
        // Default minCalls is 5; send 4 errors
        for ($i = 0; $i < 4; $i++) {
            $this->breaker->recordCall(1, 'test_tool', false);
        }
        $this->assertFalse($this->breaker->isOpen(1, 'test_tool'));
    }

    public function test_all_successes_never_trips(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->breaker->recordCall(1, 'test_tool', true);
        }
        $this->assertFalse($this->breaker->isOpen(1, 'test_tool'));
    }

    public function test_high_error_rate_trips_circuit(): void
    {
        // 5 errors out of 5 calls = 100% > default 50% threshold
        for ($i = 0; $i < 5; $i++) {
            $this->breaker->recordCall(1, 'test_tool', false);
        }
        $this->assertTrue($this->breaker->isOpen(1, 'test_tool'));
    }

    public function test_tripped_circuit_returns_true_from_record_call(): void
    {
        for ($i = 0; $i < 4; $i++) {
            $this->breaker->recordCall(1, 'test_tool', false);
        }
        $tripped = $this->breaker->recordCall(1, 'test_tool', false);
        $this->assertTrue($tripped);
    }

    public function test_reset_closes_open_circuit(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->breaker->recordCall(1, 'test_tool', false);
        }
        $this->assertTrue($this->breaker->isOpen(1, 'test_tool'));

        $this->breaker->reset(1, 'test_tool');
        $this->assertFalse($this->breaker->isOpen(1, 'test_tool'));
    }

    public function test_different_companies_have_independent_circuits(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->breaker->recordCall(1, 'test_tool', false);
        }

        $this->assertTrue($this->breaker->isOpen(1, 'test_tool'));
        $this->assertFalse($this->breaker->isOpen(2, 'test_tool'));
    }

    public function test_different_tools_have_independent_circuits(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->breaker->recordCall(1, 'tool_a', false);
        }

        $this->assertTrue($this->breaker->isOpen(1, 'tool_a'));
        $this->assertFalse($this->breaker->isOpen(1, 'tool_b'));
    }
}
