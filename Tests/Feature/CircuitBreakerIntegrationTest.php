<?php

namespace Modules\TitanZero\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Modules\TitanZero\Services\CircuitBreaker\CircuitBreakerService;
use Modules\TitanZero\Events\CircuitBreakerTripped;

/**
 * Integration tests for CircuitBreaker — verifies the event fires on sustained errors.
 */
class CircuitBreakerIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_circuit_breaker_dispatches_event_when_tripped(): void
    {
        Event::fake([CircuitBreakerTripped::class]);

        $breaker = app(CircuitBreakerService::class);

        // 5 consecutive failures — should trip the breaker
        for ($i = 0; $i < 5; $i++) {
            $breaker->recordCall(1, 'titan_zero_query', false);
        }

        Event::assertDispatched(CircuitBreakerTripped::class, function ($event) {
            return $event->companyId === 1
                && $event->toolName  === 'titan_zero_query'
                && $event->errorRate >= 50.0;
        });
    }

    public function test_circuit_breaker_event_not_dispatched_for_successes(): void
    {
        Event::fake([CircuitBreakerTripped::class]);

        $breaker = app(CircuitBreakerService::class);

        for ($i = 0; $i < 10; $i++) {
            $breaker->recordCall(1, 'titan_zero_query', true);
        }

        Event::assertNotDispatched(CircuitBreakerTripped::class);
    }

    public function test_open_circuit_blocks_repeated_recording(): void
    {
        Event::fake([CircuitBreakerTripped::class]);

        $breaker = app(CircuitBreakerService::class);

        // Trip it
        for ($i = 0; $i < 5; $i++) {
            $breaker->recordCall(1, 'tool_x', false);
        }

        $this->assertTrue($breaker->isOpen(1, 'tool_x'));

        // Subsequent records should NOT re-dispatch the event
        $breaker->recordCall(1, 'tool_x', false);
        Event::assertDispatchedTimes(CircuitBreakerTripped::class, 1);
    }
}
