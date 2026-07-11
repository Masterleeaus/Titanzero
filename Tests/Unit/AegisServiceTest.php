<?php

namespace Modules\TitanZero\Tests\Unit;

use Tests\TestCase;
use Modules\TitanZero\Services\Aegis\AegisService;

class AegisServiceTest extends TestCase
{
    private AegisService $aegis;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aegis = new AegisService();
    }

    public function test_green_verdict_for_safe_output(): void
    {
        $result = $this->aegis->evaluate('Here is a cleaning schedule for the week.');

        $this->assertSame('green', $result['verdict']);
        $this->assertEmpty($result['flags']);
        $this->assertFalse($result['escalation_required']);
        $this->assertSame('Here is a cleaning schedule for the week.', $result['safe_output']);
    }

    public function test_yellow_verdict_for_flagged_domain(): void
    {
        $result = $this->aegis->evaluate('The NDIS participant requires support with household tasks.');

        $this->assertSame('yellow', $result['verdict']);
        $this->assertContains('ndis', $result['flags']);
        $this->assertFalse($result['escalation_required']);
    }

    public function test_blocked_verdict_for_mandatory_reporting(): void
    {
        $result = $this->aegis->evaluate('There are signs of abuse and neglect that require mandatory report.');

        $this->assertSame('blocked', $result['verdict']);
        $this->assertTrue($result['escalation_required']);
        $this->assertNotEmpty($result['flags']);
        $this->assertStringContainsString('blocked by Aegis', $result['safe_output']);
    }

    public function test_compliance_gate_returns_missing_fields(): void
    {
        $context = ['company_id' => 1, 'job_id' => 5];
        $required = ['company_id', 'job_id', 'client_id', 'site_id'];

        $missing = $this->aegis->checkComplianceGate($context, $required);

        $this->assertContains('client_id', $missing);
        $this->assertContains('site_id', $missing);
        $this->assertNotContains('company_id', $missing);
    }

    public function test_compliance_gate_passes_when_all_fields_present(): void
    {
        $context = ['company_id' => 1, 'job_id' => 5, 'client_id' => 3, 'site_id' => 7];
        $required = ['company_id', 'job_id', 'client_id', 'site_id'];

        $missing = $this->aegis->checkComplianceGate($context, $required);

        $this->assertEmpty($missing);
    }
}
