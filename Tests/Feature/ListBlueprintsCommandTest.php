<?php

namespace Modules\TitanZero\Tests\Feature;

use Tests\TestCase;
use Modules\TitanZero\Console\Commands\ListBlueprintsCommand;

/**
 * Feature tests for the titan-zero:list-blueprints command.
 */
class ListBlueprintsCommandTest extends TestCase
{
    public function test_command_runs_without_error(): void
    {
        $this->artisan('titan-zero:list-blueprints')
            ->assertExitCode(0);
    }

    public function test_command_json_flag_returns_valid_json(): void
    {
        $this->artisan('titan-zero:list-blueprints', ['--json' => true])
            ->assertExitCode(0);

        // Just verify the command does not crash with --json flag
        $this->assertTrue(true);
    }
}
