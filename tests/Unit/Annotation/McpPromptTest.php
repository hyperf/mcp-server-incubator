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
use Hyperf\McpServer\Collector\Annotations\McpPrompt as Prompt;
use HyperfTest\McpServer\AbstractTestCase;

/**
 * @internal
 * @coversNothing
 */
class McpPromptTest extends AbstractTestCase
{
    public function testCreateWithValidName(): void
    {
        $prompt = new Prompt(
            name: 'valid_prompt_name',
            description: 'A valid prompt',
            server: 'test',
            version: 'v1.0'
        );

        $this->assertEquals('valid_prompt_name', $prompt->getName());
        $this->assertEquals('A valid prompt', $prompt->getDescription());
        $this->assertEquals('test', $prompt->getServer());
        $this->assertEquals('v1.0', $prompt->getVersion());
        $this->assertTrue($prompt->isEnabled());
    }

    public function testCreateWithInvalidNameThrowsException(): void
    {
        $this->expectException(ValidationError::class);
        $this->expectExceptionMessage('Prompt name must be alphanumeric, underscores, and hyphens.');

        new Prompt(name: 'invalid name with spaces');
    }

    public function testCreateWithEmptyNameUsesMethodName(): void
    {
        $prompt = new Prompt();
        $prompt->collectMethod('HyperfTest\McpServer\Stubs\TestAnnotationClass', 'testMethod');

        $this->assertEquals('testMethod', $prompt->getName());
    }

    public function testGetArgumentsGeneratesAutomatically(): void
    {
        $prompt = new Prompt();
        $prompt->collectMethod('HyperfTest\McpServer\Stubs\TestAnnotationClass', 'testMethod');

        $arguments = $prompt->getArguments();
        $this->assertIsArray($arguments);
        $this->assertCount(2, $arguments);
    }

    public function testCreateWithCustomArguments(): void
    {
        $customArguments = [
            ['name' => 'param1', 'description' => 'First parameter', 'required' => true],
            ['name' => 'param2', 'description' => 'Second parameter', 'required' => false],
        ];

        $prompt = new Prompt(arguments: $customArguments);

        $this->assertEquals($customArguments, $prompt->getArguments());
    }

    public function testDisabledPrompt(): void
    {
        $prompt = new Prompt(enabled: false);

        $this->assertFalse($prompt->isEnabled());
    }

    public function testDefaultValues(): void
    {
        $prompt = new Prompt();
        $prompt->collectMethod('HyperfTest\McpServer\Stubs\TestAnnotationClass', 'testMethod');

        $this->assertEquals('', $prompt->getDescription());
        $this->assertEquals('', $prompt->getServer());
        $this->assertEquals('', $prompt->getVersion());
        $this->assertTrue($prompt->isEnabled());
        $this->assertIsArray($prompt->getArguments());
    }

    /**
     * @dataProvider validPromptNamesProvider
     */
    public function testValidPromptNames(string $name): void
    {
        $prompt = new Prompt(name: $name);
        $this->assertEquals($name, $prompt->getName());
    }

    public static function validPromptNamesProvider(): array
    {
        return [
            ['valid_name'],
            ['valid-name'],
            ['ValidName'],
            ['prompt123'],
            ['PROMPT_NAME'],
            ['prompt-name-123'],
            ['prompt_name_123'],
        ];
    }

    /**
     * @dataProvider invalidPromptNamesProvider
     */
    public function testInvalidPromptNames(string $name): void
    {
        $this->expectException(ValidationError::class);
        new Prompt(name: $name);
    }

    public static function invalidPromptNamesProvider(): array
    {
        return [
            ['invalid name'],
            ['invalid.name'],
            ['invalid@name'],
            ['invalid#name'],
            ['invalid/name'],
        ];
    }
}
