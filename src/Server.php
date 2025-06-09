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
    public function __construct(
        protected ContainerInterface $container,
        protected McpServerManager $mcpServerManager
    ) {
    }

    public function handle(string $server = '', string $version = '1.0.0', ?RequestInterface $request = null): ResponseInterface
    {
        $request ??= $this->container->get(RequestInterface::class);

        return $this->mcpServerManager->handle($server, $version, $request);
    }
}
