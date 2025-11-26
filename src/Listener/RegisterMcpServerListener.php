<?php

namespace Hyperf\McpServer\Listener;

use Hyperf\McpServer\ServerManager;
use Psr\Container\ContainerInterface;
use Hyperf\Framework\Event\BootApplication;

class RegisterMcpServerListener implements \Hyperf\Event\Contract\ListenerInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        $manager = $this->container->get(ServerManager::class);
        $manager->register();
    }

}
