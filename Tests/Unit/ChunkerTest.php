<?php

namespace Modules\TitanZero\Tests\Unit;

use Modules\TitanZero\Services\Docs\Chunker;
use PHPUnit\Framework\TestCase;

class ChunkerTest extends TestCase
{
    public function test_chunker_splits(): void
    {
        $c = new Chunker();
        $chunks = $c->chunk(str_repeat('A', 3000), 1200);
        $this->assertGreaterThanOrEqual(2, count($chunks));
    }
}
