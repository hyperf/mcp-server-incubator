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

namespace HyperfTest\McpServer;

use Hyperf\Contract\ContainerInterface;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSourceFactory;
use Hyperf\Testing\Concerns\InteractsWithContainer;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface as PsrContainerInterface;

abstract class AbstractTestCase extends TestCase
{
    use InteractsWithContainer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createContainer();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->flushContainer();
    }

    protected function createContainer(): ContainerInterface
    {
        $container = new Container((new DefinitionSourceFactory())());

        // Register basic dependencies
        $container->set(PsrContainerInterface::class, $container);

        return $container;
    }

    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}
