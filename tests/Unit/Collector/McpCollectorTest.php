<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\McpServer\Unit\Collector;

use Dtyq\PhpMcp\Server\FastMcp\Prompts\RegisteredPrompt;
use Dtyq\PhpMcp\Server\FastMcp\Resources\RegisteredResource;
use Dtyq\PhpMcp\Server\FastMcp\Tools\RegisteredTool;
use Hyperf\McpServer\Collector\McpCollector;
use HyperfTest\McpServer\AbstractTestCase;
use ReflectionClass;
use Throwable;

/**
 * @internal
 * @coversNothing
 */
class McpCollectorTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Reset collector state for clean testing
        $this->resetCollectorState();
    }

    protected function tearDown(): void
    {
        // Reset collector state after each test
        $this->resetCollectorState();
        parent::tearDown();
    }

    public function testGetToolsReturnsArray(): void
    {
        $tools = McpCollector::getTools();

        $this->assertIsArray($tools);
        // Tools may exist from annotations in the codebase, so just verify structure
        foreach ($tools as $tool) {
            $this->assertInstanceOf(RegisteredTool::class, $tool);
        }
    }

    public function testGetToolsWithServer(): void
    {
        $mathTools = McpCollector::getTools('math');

        $this->assertIsArray($mathTools);
        // Server-specific tools may or may not exist
        foreach ($mathTools as $tool) {
            $this->assertInstanceOf(RegisteredTool::class, $tool);
        }
    }

    public function testGetPromptsReturnsArray(): void
    {
        $prompts = McpCollector::getPrompts();

        $this->assertIsArray($prompts);
        // Prompts may exist from annotations in the codebase, so just verify structure
        foreach ($prompts as $prompt) {
            $this->assertInstanceOf(RegisteredPrompt::class, $prompt);
        }
    }

    public function testGetPromptsWithServer(): void
    {
        $prompts = McpCollector::getPrompts('test');

        $this->assertIsArray($prompts);
        // Server-specific prompts may or may not exist
        foreach ($prompts as $prompt) {
            $this->assertInstanceOf(RegisteredPrompt::class, $prompt);
        }
    }

    public function testGetResourcesReturnsArray(): void
    {
        $resources = McpCollector::getResources();

        $this->assertIsArray($resources);
        // Resources may exist from annotations in the codebase, so just verify structure
        foreach ($resources as $resource) {
            $this->assertInstanceOf(RegisteredResource::class, $resource);
        }
    }

    public function testGetResourcesWithServer(): void
    {
        $resources = McpCollector::getResources('test');

        $this->assertIsArray($resources);
        // Server-specific resources may or may not exist
        foreach ($resources as $resource) {
            $this->assertInstanceOf(RegisteredResource::class, $resource);
        }
    }

    public function testCollectOnlyRunsOnce(): void
    {
        // Get reflection to check collect property
        $reflection = new ReflectionClass(McpCollector::class);
        $collectProperty = $reflection->getProperty('collect');
        $collectProperty->setAccessible(true);

        // Initially should be false
        $this->assertFalse($collectProperty->getValue());

        // Call collect
        McpCollector::collect();

        // Should now be true
        $this->assertTrue($collectProperty->getValue());

        // Reset to false to test multiple calls
        $collectProperty->setValue(false);

        // Call getTools which internally calls collect
        McpCollector::getTools();
        $this->assertTrue($collectProperty->getValue());

        // Reset and call multiple methods - collect should only run once
        $collectProperty->setValue(false);
        McpCollector::getTools();
        McpCollector::getPrompts();
        McpCollector::getResources();

        // Should still be true, indicating collect was called
        $this->assertTrue($collectProperty->getValue());
    }

    public function testCollectorHandlesEmptyAnnotations(): void
    {
        // Test that collector doesn't break with empty annotations
        $tools = McpCollector::getTools();
        $prompts = McpCollector::getPrompts();
        $resources = McpCollector::getResources();

        $this->assertIsArray($tools);
        $this->assertIsArray($prompts);
        $this->assertIsArray($resources);
    }

    public function testCollectorServerFiltering(): void
    {
        // Test server filtering works even with empty annotations
        $defaultTools = McpCollector::getTools('');
        $mathTools = McpCollector::getTools('math');
        $testTools = McpCollector::getTools('test');

        $this->assertIsArray($defaultTools);
        $this->assertIsArray($mathTools);
        $this->assertIsArray($testTools);
    }

    public function testCollectorWithServerAndVersion(): void
    {
        // Test server and version filtering
        $toolsV1 = McpCollector::getTools('test', 'v1.0');
        $toolsV2 = McpCollector::getTools('test', 'v2.0');
        $promptsV1 = McpCollector::getPrompts('test', 'v1.0');
        $resourcesV1 = McpCollector::getResources('test', 'v1.0');

        $this->assertIsArray($toolsV1);
        $this->assertIsArray($toolsV2);
        $this->assertIsArray($promptsV1);
        $this->assertIsArray($resourcesV1);
    }

    public function testCollectorMethodsDoNotThrowExceptions(): void
    {
        try {
            McpCollector::getTools();
            McpCollector::getTools('test');
            McpCollector::getPrompts();
            McpCollector::getPrompts('test');
            McpCollector::getResources();
            McpCollector::getResources('test');
            McpCollector::collect();
            $this->assertTrue(true); // If we get here, no exceptions were thrown
        } catch (Throwable $e) {
            $this->fail('Expected no exceptions, but got: ' . $e->getMessage());
        }
    }

    private function resetCollectorState(): void
    {
        $reflection = new ReflectionClass(McpCollector::class);

        $collectProperty = $reflection->getProperty('collect');
        $collectProperty->setAccessible(true);
        $collectProperty->setValue(false);

        $toolsProperty = $reflection->getProperty('tools');
        $toolsProperty->setAccessible(true);
        $toolsProperty->setValue([]);

        $promptsProperty = $reflection->getProperty('prompts');
        $promptsProperty->setAccessible(true);
        $promptsProperty->setValue([]);

        $resourcesProperty = $reflection->getProperty('resources');
        $resourcesProperty->setAccessible(true);
        $resourcesProperty->setValue([]);
    }
}
