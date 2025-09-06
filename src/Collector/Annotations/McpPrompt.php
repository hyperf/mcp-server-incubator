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

namespace Hyperf\McpServer\Collector\Annotations;

use Attribute;
use Dtyq\PhpMcp\Shared\Exceptions\ValidationError;
use Dtyq\PhpMcp\Shared\Utilities\SchemaUtils;
use Dtyq\PhpMcp\Types\Prompts\PromptArgument;

#[Attribute(Attribute::TARGET_METHOD)]
class McpPrompt extends McpAnnotation
{
    protected string $name = '';

    protected string $description = '';

    /** @var array<string, mixed> */
    protected array $arguments = [];

    protected string $server = '';

    protected string $version = '';

    protected bool $enabled = true;

    /**
     * @param array<string, mixed> $arguments
     */
    public function __construct(
        string $name = '',
        string $description = '',
        array $arguments = [],
        string $server = '',
        string $version = '',
        bool $enabled = true
    ) {
        if ($name !== '' && ! preg_match('/^[a-zA-Z0-9_-]+$/', $name)) {
            throw ValidationError::invalidFieldValue('name', 'Prompt name must be alphanumeric, underscores, and hyphens');
        }
        $this->name = $name;
        $this->description = $description;
        $this->arguments = $arguments;
        $this->server = $server;
        $this->version = $version;
        $this->enabled = $enabled;
    }

    public function getName(): string
    {
        if ($this->name === '') {
            $this->name = $this->method;
        }
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return array<string, mixed>
     */
    public function getArguments(): array
    {
        if (empty($this->arguments)) {
            $inputSchema = SchemaUtils::generateInputSchemaByClassMethod($this->class, $this->method);
            $this->arguments = $this->convertSchemaToPromptArguments($inputSchema);
        }
        return $this->arguments;
    }

    public function getServer(): string
    {
        return $this->server;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Convert input schema to prompt arguments format.
     *
     * @param array<string, mixed> $schema
     * @return array<string, mixed>
     */
    private function convertSchemaToPromptArguments(array $schema): array
    {
        $arguments = [];

        if (isset($schema['properties']) && is_array($schema['properties'])) {
            $required = $schema['required'] ?? [];

            foreach ($schema['properties'] as $paramName => $paramSchema) {
                $arguments[] = new PromptArgument(
                    $paramName,
                    $paramSchema['description'] ?? "Parameter: {$paramName}",
                    in_array($paramName, $required, true)
                );
            }
        }

        return $arguments;
    }
}
