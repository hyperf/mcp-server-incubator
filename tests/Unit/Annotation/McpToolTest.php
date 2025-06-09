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
use Hyperf\McpServer\Annotation\McpTool;
use HyperfTest\McpServer\AbstractTestCase;

/**
 * @internal
 * @coversNothing
 */
class McpToolTest extends AbstractTestCase
{
    public function testCreateWithValidName(): void
    {
        $tool = new McpTool(
            name: 'valid_tool_name',
            description: 'A valid tool',
            server: 'test'
        );

        $this->assertEquals('valid_tool_name', $tool->getName());
        $this->assertEquals('A valid tool', $tool->getDescription());
        $this->assertEquals('test', $tool->getServer());
        $this->assertTrue($tool->isEnabled());
    }

    public function testCreateWithInvalidNameThrowsException(): void
    {
        $this->expectException(ValidationError::class);
        $this->expectExceptionMessage('Tool name must be alphanumeric and underscores.');

        new McpTool(name: 'invalid-name-with-hyphens');
    }

    public function testCreateWithEmptyNameUsesMethodName(): void
    {
        $tool = new McpTool();
        $tool->collectMethod('HyperfTest\McpServer\Stubs\TestAnnotationClass', 'testMethod');

        $this->assertEquals('testMethod', $tool->getName());
    }

    public function testGetInputSchemaGeneratesAutomatically(): void
    {
        $tool = new McpTool();
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

        $tool = new McpTool(inputSchema: $customSchema);

        $this->assertEquals($customSchema, $tool->getInputSchema());
    }

    public function testDisabledTool(): void
    {
        $tool = new McpTool(enabled: false);

        $this->assertFalse($tool->isEnabled());
    }

    public function testDefaultValues(): void
    {
        $tool = new McpTool();
        $tool->collectMethod('HyperfTest\McpServer\Stubs\TestAnnotationClass', 'testMethod');

        $this->assertEquals('', $tool->getDescription());
        $this->assertEquals('default', $tool->getServer());
        $this->assertTrue($tool->isEnabled());
        $this->assertIsArray($tool->getInputSchema());
    }

    /**
     * @dataProvider validToolNamesProvider
     */
    public function testValidToolNames(string $name): void
    {
        $tool = new McpTool(name: $name);
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
        new McpTool(name: $name);
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
