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
use Dtyq\PhpMcp\Shared\Exceptions\ToolError;
use ReflectionClass;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
#[Attribute(Attribute::TARGET_METHOD)]
class McpResource extends McpAnnotation
{
    protected string $name = '';

    protected string $uri = '';

    protected string $description = '';

    protected ?string $mimeType = null;

    protected ?int $size = null;

    protected string $group = '';

    protected bool $enabled = true;

    protected bool $isTemplate = false;

    /** @var array<string, mixed> */
    protected array $uriTemplate = [];

    public function __construct(
        string $name = '',
        string $uri = '',
        string $description = '',
        ?string $mimeType = null,
        ?int $size = null,
        string $group = '',
        bool $enabled = true,
        bool $isTemplate = false,
        array $uriTemplate = [],
    ) {
        if ($name !== '' && ! preg_match('/^[a-zA-Z0-9_-]+$/', $name)) {
            throw new ToolError('Resource name must be alphanumeric, underscores, and hyphens.');
        }

        if ($uri !== '' && ! $this->isValidUri($uri)) {
            throw new ToolError('Resource URI must be a valid URI format.');
        }

        $this->name = $name;
        $this->uri = $uri;
        $this->description = $description;
        $this->mimeType = $mimeType;
        $this->size = $size;
        $this->group = $group;
        $this->enabled = $enabled;
        $this->isTemplate = $isTemplate;
        $this->uriTemplate = $uriTemplate;
    }

    public function getName(): string
    {
        if ($this->name === '') {
            $this->name = $this->method;
        }
        return $this->name;
    }

    public function getUri(): string
    {
        if (empty($this->uri)) {
            $this->uri = $this->generateDefaultUri($this->class, $this->method);
        }
        return $this->uri;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isTemplate(): bool
    {
        return $this->isTemplate;
    }

    /**
     * @return array<string, mixed>
     */
    public function getUriTemplate(): array
    {
        return $this->uriTemplate;
    }

    /**
     * Generate a default URI for the resource.
     */
    private function generateDefaultUri(string $className, ?string $methodName): string
    {
        $shortClassName = (new ReflectionClass($className))->getShortName();
        $uri = 'mcp://' . strtolower($shortClassName);

        if ($methodName) {
            $uri .= '/' . strtolower($methodName);
        }

        return $uri;
    }

    /**
     * Validate URI format.
     */
    private function isValidUri(string $uri): bool
    {
        // Basic URI validation - should start with a scheme
        return (bool) filter_var($uri, FILTER_VALIDATE_URL)
               || preg_match('/^[a-zA-Z][a-zA-Z0-9+.-]*:/', $uri);
    }
}
