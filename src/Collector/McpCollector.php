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

namespace Hyperf\McpServer\Collector;

use Dtyq\PhpMcp\Server\FastMcp\Prompts\RegisteredPrompt;
use Dtyq\PhpMcp\Server\FastMcp\Resources\RegisteredResource;
use Dtyq\PhpMcp\Server\FastMcp\Tools\RegisteredTool;
use Dtyq\PhpMcp\Types\Prompts\Prompt;
use Dtyq\PhpMcp\Types\Resources\Resource;
use Dtyq\PhpMcp\Types\Tools\Tool;
use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\McpServer\Annotation\Prompt as McpPrompt;
use Hyperf\McpServer\Annotation\Resource as McpResource;
use Hyperf\McpServer\Annotation\Tool as McpTool;
use RuntimeException;

class McpCollector
{
    protected static bool $collect = false;

    /**
     * @var array<string, array<string, RegisteredTool>>
     */
    protected static array $tools = [];

    /**
     * @var array<string, RegisteredTool>
     */
    protected static array $globalTools = [];

    /**
     * @var array<string, array<string, RegisteredPrompt>>
     */
    protected static array $prompts = [];

    /**
     * @var array<string, RegisteredPrompt>
     */
    protected static array $globalPrompts = [];

    /**
     * @return array<string, array<string, RegisteredResource>>
     */
    protected static array $resources = [];

    /**
     * @var array<string, RegisteredResource>
     */
    protected static array $globalResources = [];

    /**
     * @return array<string, RegisteredTool>
     */
    public static function getTools(string $server = '', string $version = ''): array
    {
        self::collect();
        return array_merge(
            self::$globalTools,
            self::$tools[self::createGroup($server, $version)] ?? []
        );
    }

    /**
     * @return array<string, RegisteredPrompt>
     */
    public static function getPrompts(string $server = '', string $version = ''): array
    {
        self::collect();
        return array_merge(
            self::$globalPrompts,
            self::$prompts[self::createGroup($server, $version)] ?? []
        );
    }

    /**
     * @return array<string, RegisteredResource>
     */
    public static function getResources(string $server = '', string $version = ''): array
    {
        self::collect();
        return array_merge(
            self::$globalResources,
            self::$resources[self::createGroup($server, $version)] ?? []
        );
    }

    public static function collect(): void
    {
        if (self::$collect) {
            return;
        }

        self::collectTools();
        self::collectPrompts();
        self::collectResources();

        self::$collect = true;
    }

    protected static function collectTools(): void
    {
        $mcpToolAnnotations = AnnotationCollector::getMethodsByAnnotation(McpTool::class);
        foreach ($mcpToolAnnotations as $data) {
            $class = $data['class'] ?? '';
            $method = $data['method'] ?? '';
            /** @var McpTool $mcpTool */
            $mcpTool = $data['annotation'] ?? null;
            if (empty($class) || empty($method) || empty($mcpTool)) {
                continue;
            }
            if (! $mcpTool->isEnabled()) {
                continue;
            }
            $registeredTool = new RegisteredTool(
                new Tool(
                    $mcpTool->getName(),
                    $mcpTool->getInputSchema(),
                    $mcpTool->getDescription()
                ),
                function (array $arguments) use ($class, $method) {
                    $container = ApplicationContext::getContainer();
                    if (method_exists($container, 'make')) {
                        $instance = $container->make($class);
                    }
                    if (! isset($instance) || ! method_exists($instance, $method)) {
                        throw new RuntimeException("Method {$method} does not exist in class {$class}");
                    }
                    return $instance->{$method}(...$arguments);
                }
            );
            if ($mcpTool->getServer()) {
                self::$tools[self::createGroup($mcpTool->getServer(), $mcpTool->getVersion())][$mcpTool->getName()] = $registeredTool;
            } else {
                self::$globalTools[$mcpTool->getName()] = $registeredTool;
            }
        }
    }

    protected static function collectPrompts(): void
    {
        $mcpPromptAnnotations = AnnotationCollector::getMethodsByAnnotation(McpPrompt::class);
        foreach ($mcpPromptAnnotations as $data) {
            $class = $data['class'] ?? '';
            $method = $data['method'] ?? '';
            /** @var McpPrompt $mcpPrompt */
            $mcpPrompt = $data['annotation'] ?? null;
            if (empty($class) || empty($method) || empty($mcpPrompt)) {
                continue;
            }
            if (! $mcpPrompt->isEnabled()) {
                continue;
            }
            $prompt = new Prompt(
                $mcpPrompt->getName(),
                $mcpPrompt->getDescription(),
                $mcpPrompt->getArguments(),
            );
            $registeredPrompt = new RegisteredPrompt(
                $prompt,
                function (array $arguments) use ($class, $method) {
                    $container = ApplicationContext::getContainer();
                    if (method_exists($container, 'make')) {
                        $instance = $container->make($class);
                    }
                    if (! isset($instance) || ! method_exists($instance, $method)) {
                        throw new RuntimeException("Method {$method} does not exist in class {$class}");
                    }
                    return $instance->{$method}(...$arguments);
                }
            );
            if ($mcpPrompt->getServer()) {
                self::$prompts[self::createGroup($mcpPrompt->getServer(), $mcpPrompt->getVersion())][$mcpPrompt->getName()] = $registeredPrompt;
            } else {
                self::$globalPrompts[$mcpPrompt->getName()] = $registeredPrompt;
            }
        }
    }

    protected static function collectResources(): void
    {
        $mcpResourceAnnotations = AnnotationCollector::getMethodsByAnnotation(McpResource::class);
        foreach ($mcpResourceAnnotations as $data) {
            $class = $data['class'] ?? '';
            $method = $data['method'] ?? '';
            /** @var McpResource $mcpResource */
            $mcpResource = $data['annotation'] ?? null;
            if (empty($class) || empty($method) || empty($mcpResource)) {
                continue;
            }
            if (! $mcpResource->isEnabled()) {
                continue;
            }
            $resource = new RegisteredResource(
                new Resource(
                    $mcpResource->getUri(),
                    $mcpResource->getName(),
                    $mcpResource->getDescription()
                ),
                function () use ($class, $method) {
                    $container = ApplicationContext::getContainer();
                    if (method_exists($container, 'make')) {
                        $instance = $container->make($class);
                    }
                    if (! isset($instance) || ! method_exists($instance, $method)) {
                        throw new RuntimeException("Method {$method} does not exist in class {$class}");
                    }
                    return $instance->{$method}();
                }
            );
            if ($mcpResource->getServer()) {
                self::$resources[self::createGroup($mcpResource->getServer(), $mcpResource->getVersion())][$mcpResource->getName()] = $resource;
            } else {
                self::$globalResources[$mcpResource->getUri()] = $resource;
            }
        }
    }

    private static function createGroup(string $server, string $version): string
    {
        return md5($server . $version);
    }
}
