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

use Hyperf\McpServer\Collector\Annotations\McpPrompt as Prompt;
use Hyperf\McpServer\Collector\Annotations\McpResource as Resource;
use Hyperf\McpServer\Collector\Annotations\McpTool as Tool;

class AnnotatedTestClass
{
    #[Tool(
        name: 'test_tool',
        description: 'A test tool for unit testing',
        server: 'test',
        version: 'v1.0'
    )]
    public function testTool(string $input, int $count = 1): array
    {
        return ['result' => str_repeat($input, $count)];
    }

    #[Tool(
        name: 'math_tool',
        description: 'A math calculation tool',
        server: 'math',
        version: 'v2.0'
    )]
    public function mathTool(int $a, int $b): int
    {
        return $a + $b;
    }

    #[Prompt(
        name: 'test_prompt',
        description: 'A test prompt for unit testing',
        server: 'test',
        version: 'v1.0'
    )]
    public function testPrompt(string $context, string $question): string
    {
        return "Based on context: {$context}, the answer to '{$question}' is...";
    }

    #[Resource(
        name: 'test_resource',
        uri: 'mcp://test/resource/data',
        description: 'A test resource for unit testing',
        mimeType: 'application/json',
        server: 'test',
        version: 'v1.0'
    )]
    public function testResource(): array
    {
        return ['data' => 'test resource content'];
    }

    #[Tool(
        name: 'global_tool',
        description: 'A global tool without server specification'
    )]
    public function globalTool(string $param): string
    {
        return "Global: {$param}";
    }

    #[Tool(
        name: 'disabled_tool',
        description: 'A disabled tool',
        enabled: false
    )]
    public function disabledTool(): string
    {
        return 'This should not be collected';
    }
}
