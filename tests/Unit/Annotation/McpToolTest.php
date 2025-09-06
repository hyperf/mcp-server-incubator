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

namespace HyperfTest\McpServer\Unit\Annotation;

use Dtyq\PhpMcp\Shared\Exceptions\ValidationError;
use HyperfTest\McpServer\AbstractTestCase;
use Hyperf\McpServer\Collector\Annotations\McpTool as Tool;

/**
 * @internal
 * @coversNothing
 */
class McpToolTest extends AbstractTestCase
{
    public function testCreateWithValidName(): void
    {
        $tool = new Tool(
            name: 'valid_tool_name',
            description: 'A valid tool',
            server: 'test',
            version: 'v1.0'
        );

        $this->assertEquals('valid_tool_name', $tool->getName());
        $this->assertEquals('A valid tool', $tool->getDescription());
        $this->assertEquals('test', $tool->getServer());
        $this->assertEquals('v1.0', $tool->getVersion());
        $this->assertTrue($tool->isEnabled());
    }

    public function testCreateWithInvalidNameThrowsException(): void
    {
        $this->expectException(ValidationError::class);
        $this->expectExceptionMessage('Tool name must be alphanumeric and underscores.');

        new Tool(name: 'invalid-name-with-hyphens');
    }

    public function testCreateWithEmptyNameUsesMethodName(): void
    {
        $tool = new Tool();
        $tool->collectMethod('HyperfTest\McpServer\Stubs\TestAnnotationClass', 'testMethod');

        $this->assertEquals('testMethod', $tool->getName());
    }

    public function testGetInputSchemaGeneratesAutomatically(): void
    {
        $tool = new Tool();
        $tool->collectMethod('HyperfTest\McpServer\Stubs\TestAnnotationClass', 'testMethod');

        $schema = $tool->getInputSchema();
        $this->assertIsArray($schema);
        $this->assertArrayHasKey('type', $schema);
        $this->assertEquals('object', $schema['type']);
    }

    public function testCreateWithCustomInputSchema(): void
    {
        $customSchema = [
            'type' => 'object',
            'properties' => [
                'param1' => ['type' => 'string'],
                'param2' => ['type' => 'integer'],
            ],
        ];

        $tool = new Tool(inputSchema: $customSchema);

        $this->assertEquals($customSchema, $tool->getInputSchema());
    }

    public function testDisabledTool(): void
    {
        $tool = new Tool(enabled: false);

        $this->assertFalse($tool->isEnabled());
    }

    public function testDefaultValues(): void
    {
        $tool = new Tool();
        $tool->collectMethod('HyperfTest\McpServer\Stubs\TestAnnotationClass', 'testMethod');

        $this->assertEquals('', $tool->getDescription());
        $this->assertEquals('', $tool->getServer());
        $this->assertEquals('', $tool->getVersion());
        $this->assertTrue($tool->isEnabled());
        $this->assertIsArray($tool->getInputSchema());
    }

    /**
     * @dataProvider validToolNamesProvider
     */
    public function testValidToolNames(string $name): void
    {
        $tool = new Tool(name: $name);
        $this->assertEquals($name, $tool->getName());
    }

    public static function validToolNamesProvider(): array
    {
        return [
            ['valid_name'],
            ['ValidName'],
            ['tool123'],
            ['TOOL_NAME'],
            ['tool_name_123'],
        ];
    }

    /**
     * @dataProvider invalidToolNamesProvider
     */
    public function testInvalidToolNames(string $name): void
    {
        $this->expectException(ValidationError::class);
        new Tool(name: $name);
    }

    public static function invalidToolNamesProvider(): array
    {
        return [
            ['invalid-name'],
            ['invalid name'],
            ['invalid.name'],
            ['invalid@name'],
            ['invalid#name'],
        ];
    }
}
