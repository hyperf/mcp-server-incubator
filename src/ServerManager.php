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
use Hyperf\HttpServer\Router\Router;
use Mcp\Server;
use Mcp\Server\Builder;
use Mcp\Server\Transport\StdioTransport;
use Mcp\Server\Transport\StreamableHttpTransport;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;

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

        foreach ($servers as $name => $options) {
            $server = $this->buildServer($options);
            match ($options['type'] ?? '') {
                ServerType::HTTP->value => $this->connectHttpTransport($server, $options),
                ServerType::STDIO->value => $this->connectStdioTransport($server, $options),
            };
        }
    }

    protected function buildServer(array $options): Server
    {
        return (new Builder())
            ->setContainer($this->container)
            ->build();
    }

    protected function connectHttpTransport(Server $server, array $options): void
    {
        Router::addRoute(
            ['GET', 'POST'],
            $options['path'] ?? '/mcp',
            function (RequestInterface $request) use ($server) {
                return $server->run(new StreamableHttpTransport($request));
            },
            $options['options'] ?? []
        );
    }

    protected function connectStdioTransport(Server $server, array $options): void
    {
        $command = new class($this->container, $server, $options['name'] ?? 'mcp:run', $options['description'] ?? 'A demo stdio mcp server command.') extends Command {
            public function __construct(
                protected ContainerInterface $container,
                protected Server $server,
                protected ?string $name,
                protected string $description
            ) {
                parent::__construct($name);
            }

            public function configure(): void
            {
                $this->setDescription($this->description);
            }

            public function handle(): int
            {
                $this->output->writeln('MCP Stdio Server is running...');

                $transport = new StdioTransport();
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
