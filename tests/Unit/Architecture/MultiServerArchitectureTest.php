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

namespace HyperfTest\McpServer\Unit\Architecture;

use Hyperf\McpServer\Collector\Annotations\McpPrompt as Prompt;
use Hyperf\McpServer\Collector\Annotations\McpResource as Resource;
use Hyperf\McpServer\Collector\Annotations\McpTool as Tool;
use HyperfTest\McpServer\AbstractTestCase;

/**
 * @internal
 * @coversNothing
 */
class MultiServerArchitectureTest extends AbstractTestCase
{
    public function testToolAnnotationSupportsServerVersioning(): void
    {
        $toolV1 = new Tool(
            name: 'api_tool',
            description: 'API tool version 1',
            server: 'api',
            version: 'v1.0'
        );

        $toolV2 = new Tool(
            name: 'api_tool',
            description: 'API tool version 2',
            server: 'api',
            version: 'v2.0'
        );

        $this->assertEquals('api', $toolV1->getServer());
        $this->assertEquals('v1.0', $toolV1->getVersion());
        $this->assertEquals('api', $toolV2->getServer());
        $this->assertEquals('v2.0', $toolV2->getVersion());

        // Same name but different versions should be allowed
        $this->assertEquals('api_tool', $toolV1->getName());
        $this->assertEquals('api_tool', $toolV2->getName());
    }

    public function testPromptAnnotationSupportsServerVersioning(): void
    {
        $promptV1 = new Prompt(
            name: 'chat_prompt',
            description: 'Chat prompt version 1',
            server: 'chat',
            version: 'v1.0'
        );

        $promptV2 = new Prompt(
            name: 'chat_prompt',
            description: 'Chat prompt version 2',
            server: 'chat',
            version: 'v2.0'
        );

        $this->assertEquals('chat', $promptV1->getServer());
        $this->assertEquals('v1.0', $promptV1->getVersion());
        $this->assertEquals('chat', $promptV2->getServer());
        $this->assertEquals('v2.0', $promptV2->getVersion());
    }

    public function testResourceAnnotationSupportsServerVersioning(): void
    {
        $resourceV1 = new Resource(
            name: 'data_resource',
            uri: 'mcp://data/v1/resource',
            description: 'Data resource version 1',
            server: 'data',
            version: 'v1.0'
        );

        $resourceV2 = new Resource(
            name: 'data_resource',
            uri: 'mcp://data/v2/resource',
            description: 'Data resource version 2',
            server: 'data',
            version: 'v2.0'
        );

        $this->assertEquals('data', $resourceV1->getServer());
        $this->assertEquals('v1.0', $resourceV1->getVersion());
        $this->assertEquals('data', $resourceV2->getServer());
        $this->assertEquals('v2.0', $resourceV2->getVersion());
    }

    public function testBackwardCompatibilityWithEmptyServer(): void
    {
        $globalTool = new Tool(
            name: 'global_tool',
            description: 'A tool without server specification'
        );

        $globalPrompt = new Prompt(
            name: 'global_prompt',
            description: 'A prompt without server specification'
        );

        $globalResource = new Resource(
            name: 'global_resource',
            uri: 'mcp://global/resource',
            description: 'A resource without server specification'
        );

        // Should work without server/version
        $this->assertEquals('', $globalTool->getServer());
        $this->assertEquals('', $globalTool->getVersion());
        $this->assertEquals('', $globalPrompt->getServer());
        $this->assertEquals('', $globalPrompt->getVersion());
        $this->assertEquals('', $globalResource->getServer());
        $this->assertEquals('', $globalResource->getVersion());
    }

    public function testAnnotationParameterValidation(): void
    {
        // Test that all annotation parameters work correctly
        $tool = new Tool(
            name: 'test_tool',
            description: 'Test tool with all parameters',
            inputSchema: ['type' => 'object', 'properties' => ['param' => ['type' => 'string']]],
            server: 'test_server',
            version: 'v1.0',
            enabled: true
        );

        $this->assertEquals('test_tool', $tool->getName());
        $this->assertEquals('Test tool with all parameters', $tool->getDescription());
        $this->assertEquals('test_server', $tool->getServer());
        $this->assertEquals('v1.0', $tool->getVersion());
        $this->assertTrue($tool->isEnabled());
        $this->assertArrayHasKey('type', $tool->getInputSchema());
    }

    public function testDisabledAnnotations(): void
    {
        $disabledTool = new Tool(
            name: 'disabled_tool',
            description: 'This tool is disabled',
            enabled: false
        );

        $disabledPrompt = new Prompt(
            name: 'disabled_prompt',
            description: 'This prompt is disabled',
            enabled: false
        );

        $disabledResource = new Resource(
            name: 'disabled_resource',
            uri: 'mcp://disabled/resource',
            description: 'This resource is disabled',
            enabled: false
        );

        $this->assertFalse($disabledTool->isEnabled());
        $this->assertFalse($disabledPrompt->isEnabled());
        $this->assertFalse($disabledResource->isEnabled());
    }
}
