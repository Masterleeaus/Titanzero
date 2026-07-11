<?php

namespace Modules\TitanZero\Tests\Feature;

use Tests\TestCase;

/**
 * TitanZero core module smoke tests.
 * Validates that all key classes, services, and manifests exist and are loadable.
 */
class TitanZeroCoreTest extends TestCase
{
    public function test_query_service_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanZero\Services\TitanZeroQueryService::class),
            'TitanZeroQueryService must exist'
        );
    }

    public function test_aegis_service_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanZero\Services\Aegis\AegisService::class),
            'AegisService must exist'
        );
    }

    public function test_context_loader_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanZero\Services\Context\TitanZeroContextLoader::class),
            'TitanZeroContextLoader must exist'
        );
    }

    public function test_company_api_key_service_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanZero\Services\CompanyApiKeyService::class),
            'CompanyApiKeyService must exist'
        );
    }

    public function test_rate_limit_service_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanZero\Services\TitanZeroRateLimitService::class),
            'TitanZeroRateLimitService must exist'
        );
    }

    public function test_facade_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanZero\Support\Facades\TitanZero::class),
            'TitanZero facade must exist'
        );
    }

    public function test_company_ai_key_entity_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanZero\Entities\CompanyAiKey::class),
            'CompanyAiKey entity must exist'
        );
    }

    public function test_ai_call_entity_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanZero\Entities\TitanZeroAiCall::class),
            'TitanZeroAiCall entity must exist'
        );
    }

    public function test_filament_plugin_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanZero\Filament\Plugin\TitanZeroPlugin::class),
            'TitanZeroPlugin must exist'
        );
    }

    public function test_chat_page_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanZero\Filament\Pages\TitanZeroChatPage::class),
            'TitanZeroChatPage must exist'
        );
    }

    public function test_ai_call_resource_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanZero\Filament\Resources\TitanZeroAiCallResource::class),
            'TitanZeroAiCallResource must exist'
        );
    }

    public function test_embed_widget_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanZero\Filament\Widgets\TitanZeroEmbedWidget::class),
            'TitanZeroEmbedWidget must exist'
        );
    }

    public function test_ai_tools_manifest_exists(): void
    {
        $manifestPath = module_path('TitanZero', 'manifests/ai_tools.json');

        $this->assertFileExists($manifestPath, 'ai_tools.json manifest must exist in manifests/');

        $manifest = json_decode(file_get_contents($manifestPath), true);
        $this->assertIsArray($manifest);
        $this->assertSame('TitanZero', $manifest['module']);
        $this->assertArrayHasKey('tools', $manifest);
        $this->assertNotEmpty($manifest['tools']);
    }

    public function test_query_service_bound_in_container(): void
    {
        $service = app('titan-zero.query');

        $this->assertInstanceOf(
            \Modules\TitanZero\Services\TitanZeroQueryService::class,
            $service
        );
    }

    public function test_circuit_breaker_service_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanZero\Services\CircuitBreaker\CircuitBreakerService::class),
            'CircuitBreakerService must exist'
        );
    }

    public function test_tool_invocation_logger_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanZero\Services\ToolInvocationLogger::class),
            'ToolInvocationLogger must exist'
        );
    }

    public function test_cross_tenant_guard_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanZero\Services\Security\CrossTenantGuard::class),
            'CrossTenantGuard must exist'
        );
    }

    public function test_agent_evaluator_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanZero\Evaluation\AgentEvaluator::class),
            'AgentEvaluator must exist'
        );
    }

    public function test_agent_evaluation_page_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanZero\Filament\Pages\AgentEvaluationPage::class),
            'AgentEvaluationPage must exist'
        );
    }
}
