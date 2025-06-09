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

use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class Server
{
    protected ContainerInterface $container;

    protected McpServerManager $mcpServerManager;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->mcpServerManager = $container->get(McpServerManager::class);
    }

    public function handle(string $server = '', ?RequestInterface $request = null): ResponseInterface
    {
        $request ??= $this->container->get(RequestInterface::class);

        return $this->mcpServerManager->handle($server, $request);
    }
}
