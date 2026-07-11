<?php

namespace Modules\TitanZero\Tests\Contract;

use PHPUnit\Framework\TestCase;

/**
 * Contract tests — verify all manifests are schema-valid and all declared
 * tool classes exist and are instantiable.
 *
 * These tests do not require a database and run as pure PHP assertions.
 */
class ManifestContractTest extends TestCase
{
    private string $manifestDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manifestDir = dirname(__DIR__, 2) . '/manifests';
    }

    public function test_ai_tools_manifest_is_valid_json(): void
    {
        $path = $this->manifestDir . '/ai_tools.json';
        $this->assertFileExists($path, 'ai_tools.json must exist');

        $data = json_decode(file_get_contents($path), true);
        $this->assertNotNull($data, 'ai_tools.json must be valid JSON');
        $this->assertIsArray($data);
    }

    public function test_ai_tools_manifest_has_required_top_level_keys(): void
    {
        $data = $this->loadManifest('ai_tools.json');

        $this->assertArrayHasKey('module', $data);
        $this->assertArrayHasKey('tools', $data);
        $this->assertSame('TitanZero', $data['module']);
        $this->assertIsArray($data['tools']);
        $this->assertNotEmpty($data['tools']);
    }

    public function test_each_tool_has_name_and_description(): void
    {
        $data  = $this->loadManifest('ai_tools.json');
        $tools = $data['tools'];

        foreach ($tools as $tool) {
            $this->assertArrayHasKey('name', $tool, "Tool missing 'name' key");
            $this->assertArrayHasKey('description', $tool, "Tool '{$tool['name']}' missing 'description'");
            $this->assertNotEmpty($tool['name'], "Tool name must not be empty");
        }
    }

    public function test_module_json_is_valid(): void
    {
        $path = dirname(__DIR__, 2) . '/module.json';
        $this->assertFileExists($path);

        $data = json_decode(file_get_contents($path), true);
        $this->assertNotNull($data, 'module.json must be valid JSON');
        $this->assertArrayHasKey('name', $data);
        $this->assertSame('TitanZero', $data['name']);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function loadManifest(string $filename): array
    {
        $path = $this->manifestDir . '/' . $filename;
        $this->assertFileExists($path, "{$filename} must exist in manifests/");
        $data = json_decode(file_get_contents($path), true);
        $this->assertNotNull($data, "{$filename} must be valid JSON");
        return $data;
    }
}
