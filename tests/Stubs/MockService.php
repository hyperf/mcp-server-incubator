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

namespace HyperfTest\McpServer\Stubs;

use Hyperf\McpServer\Annotation\Prompt;
use Hyperf\McpServer\Annotation\Resource;
use Hyperf\McpServer\Annotation\Tool;

class MockService
{
    #[Tool(
        name: 'test_tool',
        description: 'A test tool for unit testing',
        server: 'test'
    )]
    public function testTool(string $input): string
    {
        return "Processed: {$input}";
    }

    #[Tool(
        name: 'math_add',
        description: 'Add two numbers',
        server: 'math'
    )]
    public function addNumbers(int $a, int $b): int
    {
        return $a + $b;
    }

    #[Prompt(
        name: 'test_prompt',
        description: 'A test prompt for unit testing',
        server: 'test'
    )]
    public function testPrompt(string $topic): string
    {
        return "Please provide information about: {$topic}";
    }

    #[Resource(
        name: 'test_resource',
        uri: 'mcp://test/resource',
        description: 'A test resource for unit testing',
        mimeType: 'application/json',
        server: 'test'
    )]
    public function testResource(): array
    {
        return [
            'id' => 1,
            'name' => 'Test Resource',
            'data' => ['key' => 'value'],
        ];
    }

    #[Tool(
        name: 'disabled_tool',
        description: 'A disabled tool',
        enabled: false
    )]
    public function disabledTool(): string
    {
        return 'This should not be called';
    }
}
