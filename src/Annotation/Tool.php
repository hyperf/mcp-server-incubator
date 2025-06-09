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

namespace Hyperf\McpServer\Annotation;

use Attribute;
use Dtyq\PhpMcp\Shared\Exceptions\ValidationError;
use Dtyq\PhpMcp\Shared\Utilities\SchemaUtils;

#[Attribute(Attribute::TARGET_METHOD)]
class Tool extends McpAnnotation
{
    public function __construct(
        protected string $name = '',
        protected string $description = '',
        protected array $inputSchema = [],
        protected string $server = 'default',
        protected bool $enabled = true,
    ) {
        if ($name !== '' && ! preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
            throw new ValidationError('Tool name must be alphanumeric and underscores.');
        }
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
    public function getInputSchema(): array
    {
        if (empty($this->inputSchema)) {
            $this->inputSchema = SchemaUtils::generateInputSchemaByClassMethod($this->class, $this->method);
        }
        return $this->inputSchema;
    }

    public function getServer(): string
    {
        return $this->server;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
