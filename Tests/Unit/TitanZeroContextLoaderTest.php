<?php

namespace Modules\TitanZero\Tests\Unit;

use Tests\TestCase;
use Modules\TitanZero\Services\Context\TitanZeroContextLoader;

class TitanZeroContextLoaderTest extends TestCase
{
    private TitanZeroContextLoader $loader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loader = new TitanZeroContextLoader();
    }

    public function test_load_returns_cleaning_pack_by_default(): void
    {
        $pack = $this->loader->load(null, []);

        $this->assertArrayHasKey('knowledge', $pack);
        $this->assertArrayHasKey('terminology', $pack);
        $this->assertArrayHasKey('compliance', $pack);
        $this->assertStringContainsStringIgnoringCase('clean', $pack['knowledge']);
    }

    public function test_load_returns_ndis_pack_when_vertical_specified(): void
    {
        $pack = $this->loader->load(null, ['verticals' => ['ndis']]);

        $this->assertStringContainsStringIgnoringCase('ndis', $pack['knowledge']);
        $this->assertStringContainsStringIgnoringCase('ndis', $pack['compliance']);
    }

    public function test_load_merges_multiple_verticals(): void
    {
        $pack = $this->loader->load(null, ['verticals' => ['cleaning', 'ndis']]);

        $this->assertStringContainsStringIgnoringCase('clean', $pack['knowledge']);
        $this->assertStringContainsStringIgnoringCase('ndis', $pack['knowledge']);
        $this->assertSame(['cleaning', 'ndis'], $pack['verticals']);
    }

    public function test_to_system_prompt_includes_all_sections(): void
    {
        $pack   = $this->loader->load(null, ['verticals' => ['cleaning']]);
        $prompt = $this->loader->toSystemPrompt($pack);

        $this->assertStringContainsString('INDUSTRY KNOWLEDGE:', $prompt);
        $this->assertStringContainsString('KEY TERMINOLOGY:', $prompt);
        $this->assertStringContainsString('COMPLIANCE & STANDARDS:', $prompt);
        $this->assertStringContainsString('CHECKLIST INTELLIGENCE:', $prompt);
    }

    public function test_runtime_context_attached_to_pack(): void
    {
        $pack = $this->loader->load(42, ['job_id' => 7, 'client_id' => 3]);

        $this->assertSame(42, $pack['runtime']['company_id']);
        $this->assertSame(7, $pack['runtime']['job_id']);
        $this->assertSame(3, $pack['runtime']['client_id']);
    }
}
