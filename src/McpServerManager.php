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
    protected ContainerInterface $container;

    protected Application $application;

    protected AuthenticatorInterface $authenticator;

    protected SessionManagerInterface $sessionManager;

    /**
     * @var array<string, McpServer>
     */
    protected array $mcpServers = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->application = $container->get(Application::class);
        $this->authenticator = $container->get(AuthenticatorInterface::class);
        $this->sessionManager = $container->get(SessionManagerInterface::class);
    }

    public function handle(string $group = '', ?RequestInterface $request = null): ResponseInterface
    {
        $request ??= $this->container->get(RequestInterface::class);
        return $this->get($group)->http($request, $this->sessionManager, $this->authenticator);
    }

    public function get(string $group = ''): McpServer
    {
        if (! isset($this->mcpServers[$group])) {
            $mcpServer = new McpServer('McpServer', '1.0.0', $this->application);
            $this->mcpServers[$group] = $mcpServer;

            $this->addAnnotationTools($mcpServer, $group);
            $this->addAnnotationPrompts($mcpServer, $group);
            $this->addAnnotationResources($mcpServer, $group);
        }

        return $this->mcpServers[$group];
    }

    protected function addAnnotationTools(McpServer $mcpServer, string $group = ''): void
    {
        $registeredTools = McpCollector::getTools($group);
        foreach ($registeredTools as $registeredTool) {
            $mcpServer->registerTool($registeredTool);
        }
    }

    protected function addAnnotationPrompts(McpServer $mcpServer, string $group = ''): void
    {
        $registeredPrompts = McpCollector::getPrompts($group);
        foreach ($registeredPrompts as $registeredPrompt) {
            $mcpServer->registerPrompt($registeredPrompt);
        }
    }

    protected function addAnnotationResources(McpServer $mcpServer, string $group = ''): void
    {
        $registeredResources = McpCollector::getResources($group);
        foreach ($registeredResources as $registeredResource) {
            $mcpServer->registerResource($registeredResource);
        }
    }
}
