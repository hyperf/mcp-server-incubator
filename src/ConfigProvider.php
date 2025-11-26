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

namespace Hyperf\McpServer;

use Mcp\Server\Session\InMemorySessionStore;
use Mcp\Server\Session\SessionInterface;
use Psr\Container\ContainerInterface;

class ConfigProvider
{
    public function __invoke(ContainerInterface $container): array
    {
        return [
            'dependencies' => [
                SessionInterface::class => fn ($container) => new InMemorySessionStore(3600),
            ],
        ];
    }
}
