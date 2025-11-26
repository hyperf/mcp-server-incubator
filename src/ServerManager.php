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

use Hyperf\Command\Command;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\HttpServer\Router\Router;
use Mcp\Server;
use Mcp\Server\Builder;
use Mcp\Server\Session\SessionInterface;
use Mcp\Server\Transport\StdioTransport;
use Mcp\Server\Transport\StreamableHttpTransport;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

class ServerManager
{
    protected array $servers = [];

    public function __construct(
        protected ContainerInterface $container,
        protected ConfigInterface $config
    ) {
    }

    public function register()
    {
        $servers = $this->config->get('mcp.servers', []);

        foreach ($servers as $options) {
            if (! ($options['enabled'] ?? true)) {
                continue;
            }
            $server = $this->buildServer($options);
            ! empty($options['router'] ?? '') && $this->registerRouter($server, $options['router'] ?? []);
            ! empty($options['command'] ?? '') && $this->registerCommand($server, $options['command'] ?? []);
        }
    }

    protected function buildServer(array $options): Server
    {
        $builder = (new Builder())
            ->setServerInfo(
                name: $options['name'] ?? 'MCP Server',
                version: $options['version'] ?? '1.0.0',
                description: $options['description'] ?? 'A MCP server.'
            )
            ->setContainer($this->container)
            ->setDiscovery(
                $options['discovery']['base_path'] ?? BASE_PATH,
                $options['discovery']['scan_dirs'] ?? ['.', 'src', 'app'],
                $options['discovery']['exclude_dirs'] ?? ['vendor', 'tests']
            );

        if (! ($options['event_enabled'] ?? false)) {
            $builder->setEventDispatcher($this->container->get(EventDispatcherInterface::class));
        }

        if ($this->container->has(SessionInterface::class)) {
            $builder->setSession($this->container->get(SessionInterface::class));
        }

        return $builder->build();
    }

    protected function registerRouter(Server $server, array $options): void
    {
        Router::addRoute(
            ['GET', 'POST', 'OPTIONS', 'DELETE'],
            $options['path'] ?? '/mcp',
            function (RequestInterface $request) use ($server) {
                return $server->run(new StreamableHttpTransport($request));
            },
            $options['options'] ?? []
        );
    }

    protected function registerCommand(Server $server, array $options): void
    {
        $command = new class($this->container, $server, $options) extends Command {
            protected ?LoggerInterface $logger = null;

            public function __construct(
                protected ContainerInterface $container,
                protected Server $server,
                protected array $options
            ) {
                $this->signature = $this->options['signature'] ?? 'mcp:stdio';
                $this->description = $this->options['description'] ?? 'Run the MCP stdio server.';
                if ($this->container->has(StdoutLoggerInterface::class)) {
                    $this->logger = $this->container->get(StdoutLoggerInterface::class);
                }
                parent::__construct();
            }

            public function handle(): int
            {
                $transport = new StdioTransport(logger: $this->logger);

                return $this->server->run($transport);
            }
        };

        $commandId = spl_object_id($command);
        $this->container->bind($commandId, $command);

        $commands = $this->config->get('commands', []);
        $commands[] = $commandId;
        $this->config->set('commands', $commands);
    }
}
