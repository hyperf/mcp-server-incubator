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
return [
    'servers' => [
        [
            'enabled' => true,
            'name' => 'HTTP MCP Server',
            'description' => 'An HTTP MCP server.',
            'version' => '1.0.0',
            // Options for class discovery
            'discovery' => [
                'base_path' => BASE_PATH,
                'scan_dirs' => ['.', 'src', 'app'],
                'exclude_dirs' => ['vendor', 'tests'],
            ],
            // Whether to enable event handling
            'event_enabled' => false,
            // Options specific to HTTP transport
            'router' => [
                'path' => '/mcp',
                'options' => [],
            ],
            // Options specific to STDIO transport
            'command' => [
                'signature' => 'mcp',
                'description' => 'A mcp server command.',
            ],
        ],
    ],
];
