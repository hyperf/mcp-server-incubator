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

use Dtyq\PhpMcp\Server\McpServer;
use Dtyq\PhpMcp\Server\Transports\Http\SessionManagerInterface;
use Dtyq\PhpMcp\Shared\Auth\AuthenticatorInterface;
use Dtyq\PhpMcp\Shared\Kernel\Application;
use Hyperf\McpServer\Collector\McpCollector;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class McpServerManager
{
    /**
     * @var array<string, array<string, McpServer>>
     */
    protected array $mcpServers = [];

    public function __construct(
        protected ContainerInterface $container,
        protected Application $application,
        protected AuthenticatorInterface $authenticator,
        protected SessionManagerInterface $sessionManager,
    ) {
    }

    public function handle(string $server = '', string $version = '1.0.0', ?RequestInterface $request = null): ResponseInterface
    {
        $request ??= $this->container->get(RequestInterface::class);

        return $this->get($server, $version)->http($request, $this->sessionManager, $this->authenticator);
    }

    public function get(string $server = '', string $version = '1.0.0'): McpServer
    {
        if (! isset($this->mcpServers[$server][$version])) {
            $mcpServer = $this->createMcpServer('McpServer', $server, $version);
            $this->addAnnotationTools($mcpServer, $server, $version);
            $this->addAnnotationPrompts($mcpServer, $server, $version);
            $this->addAnnotationResources($mcpServer, $server, $version);
            $this->mcpServers[$server][$version] = $mcpServer;
        }

        return $this->mcpServers[$server][$version];
    }

    public function createMcpServer(string $name = 'McpServer', string $version = '1.0.0'): McpServer
    {
        return new McpServer($name, $version, $this->application);
    }

    protected function addAnnotationTools(McpServer $mcpServer, string $server, string $version): void
    {
        $registeredTools = McpCollector::getTools($server, $version);
        foreach ($registeredTools as $registeredTool) {
            $mcpServer->registerTool($registeredTool);
        }
    }

    protected function addAnnotationPrompts(McpServer $mcpServer, string $server, string $version): void
    {
        $registeredPrompts = McpCollector::getPrompts($server, $version);
        foreach ($registeredPrompts as $registeredPrompt) {
            $mcpServer->registerPrompt($registeredPrompt);
        }
    }

    protected function addAnnotationResources(McpServer $mcpServer, string $server, string $version): void
    {
        $registeredResources = McpCollector::getResources($server, $version);
        foreach ($registeredResources as $registeredResource) {
            $mcpServer->registerResource($registeredResource);
        }
    }
}
