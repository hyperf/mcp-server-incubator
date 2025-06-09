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

/**
 * @Annotation
 * @Target({"METHOD"})
 */
#[Attribute(Attribute::TARGET_METHOD)]
class McpTool extends McpAnnotation
{
    protected string $name = '';

    protected string $description = '';

    /** @var array<string, mixed> */
    protected array $inputSchema = [];

    protected string $group = '';

    protected bool $enabled = true;

    /**
     * @param array<string, mixed> $inputSchema
     */
    public function __construct(
        string $name = '',
        string $description = '',
        array $inputSchema = [],
        string $group = '',
        bool $enabled = true,
    ) {
        if ($name !== '' && ! preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
            throw new ValidationError('Tool name must be alphanumeric and underscores.');
        }
        $this->name = $name;
        $this->description = $description;
        $this->inputSchema = $inputSchema;
        $this->group = $group;
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
    public function getInputSchema(): array
    {
        if (empty($this->inputSchema)) {
            $this->inputSchema = SchemaUtils::generateInputSchemaByClassMethod($this->class, $this->method);
        }
        return $this->inputSchema;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
