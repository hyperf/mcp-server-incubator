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
use Hyperf\Context\ApplicationContext;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\McpServer\Collector\McpCollector;
use Psr\Http\Message\ResponseInterface;

class Server
{
    public function handler(string $group = ''): ResponseInterface
    {
        $container = ApplicationContext::getContainer();
        $request = $container->get(RequestInterface::class);

        $authenticator = $container->get(AuthenticatorInterface::class);
        $sessionManager = $container->get(SessionManagerInterface::class);
        $app = new Application($container);
        $mcpServer = new McpServer('McpServer', '1.0.0', $app);

        $this->addAnnotationTools($mcpServer, $group);
        $this->addAnnotationPrompts($mcpServer, $group);
        $this->addAnnotationResources($mcpServer, $group);

        return $mcpServer->http($request, $sessionManager, $authenticator);
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
