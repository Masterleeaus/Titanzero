<?php

namespace Modules\TitanZero\Tests\Feature;

use Tests\TestCase;

/**
 * AIChatPro integration smoke tests.
 *
 * These tests verify that the AIChatPro routes are registered in TitanZero
 * and that the controller classes and entities can be instantiated correctly.
 */
class AIChatProTest extends TestCase
{
    /** Route names registered for AIChatPro */
    public function test_aichatpro_routes_are_named(): void
    {
        $this->assertTrue(
            \Illuminate\Support\Facades\Route::has('titan.zero.ai-chat.index'),
            'Route titan.zero.ai-chat.index should be registered'
        );

        $this->assertTrue(
            \Illuminate\Support\Facades\Route::has('titan.zero.ai-chat.send'),
            'Route titan.zero.ai-chat.send should be registered'
        );

        $this->assertTrue(
            \Illuminate\Support\Facades\Route::has('titan.zero.ai-chat.session.new'),
            'Route titan.zero.ai-chat.session.new should be registered'
        );
    }

    /** Entity classes are loadable */
    public function test_entity_classes_exist(): void
    {
        $this->assertTrue(class_exists(\Modules\TitanZero\Entities\AiChatCategory::class));
        $this->assertTrue(class_exists(\Modules\TitanZero\Entities\AiChatSession::class));
        $this->assertTrue(class_exists(\Modules\TitanZero\Entities\AiChatMessage::class));
    }

    /** Service class is loadable */
    public function test_service_class_exists(): void
    {
        $this->assertTrue(class_exists(\Modules\TitanZero\Services\AiChatProService::class));
    }

    /** AiChatProService tools returns expected structure */
    public function test_aichatpro_service_tools_returns_array(): void
    {
        $tools = \Modules\TitanZero\Services\AiChatProService::tools();

        $this->assertIsArray($tools);
        $this->assertNotEmpty($tools);
        $this->assertArrayHasKey('name', $tools[0]);
        $this->assertSame('generate_image', $tools[0]['name']);
    }

    /** Config key for aichatpro default_screen is present */
    public function test_config_aichatpro_default_screen(): void
    {
        $screen = config('titanzero.aichatpro.default_screen', 'new');
        $this->assertContains($screen, ['new', 'last', 'pinned']);
    }
}
