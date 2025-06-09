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

namespace HyperfTest\McpServer\Unit\Annotation;

use Dtyq\PhpMcp\Shared\Exceptions\ToolError;
use Hyperf\McpServer\Annotation\Resource;
use HyperfTest\McpServer\AbstractTestCase;

/**
 * @internal
 * @coversNothing
 */
class McpResourceTest extends AbstractTestCase
{
    public function testCreateWithValidParameters(): void
    {
        $resource = new Resource(
            name: 'valid_resource',
            uri: 'mcp://test/resource',
            description: 'A valid resource',
            mimeType: 'application/json',
            size: 1024,
            server: 'test'
        );

        $this->assertEquals('valid_resource', $resource->getName());
        $this->assertEquals('mcp://test/resource', $resource->getUri());
        $this->assertEquals('A valid resource', $resource->getDescription());
        $this->assertEquals('application/json', $resource->getMimeType());
        $this->assertEquals(1024, $resource->getSize());
        $this->assertEquals('test', $resource->getServer());
        $this->assertTrue($resource->isEnabled());
        $this->assertFalse($resource->isTemplate());
    }

    public function testCreateWithInvalidNameThrowsException(): void
    {
        $this->expectException(ToolError::class);
        $this->expectExceptionMessage('Resource name must be alphanumeric, underscores, and hyphens.');

        new Resource(name: 'invalid name with spaces');
    }

    public function testCreateWithInvalidUriThrowsException(): void
    {
        $this->expectException(ToolError::class);
        $this->expectExceptionMessage('Resource URI must be a valid URI format.');

        new Resource(uri: 'invalid-uri');
    }

    public function testCreateWithEmptyNameUsesMethodName(): void
    {
        $resource = new Resource();
        $resource->collectMethod('HyperfTest\McpServer\Stubs\TestAnnotationClass', 'testMethod');

        $this->assertEquals('testMethod', $resource->getName());
    }

    public function testGenerateDefaultUri(): void
    {
        $resource = new Resource();
        $resource->collectMethod('HyperfTest\McpServer\Stubs\TestAnnotationClass', 'testMethod');

        $uri = $resource->getUri();
        $this->assertStringContainsString('testannotationclass', strtolower($uri));
        $this->assertStringContainsString('testmethod', strtolower($uri));
    }

    public function testCreateWithTemplate(): void
    {
        $uriTemplate = ['param1' => 'string', 'param2' => 'integer'];
        $resource = new Resource(
            isTemplate: true,
            uriTemplate: $uriTemplate
        );

        $this->assertTrue($resource->isTemplate());
        $this->assertEquals($uriTemplate, $resource->getUriTemplate());
    }

    public function testDisabledResource(): void
    {
        $resource = new Resource(enabled: false);

        $this->assertFalse($resource->isEnabled());
    }

    public function testDefaultValues(): void
    {
        $resource = new Resource();

        $this->assertEquals('', $resource->getDescription());
        $this->assertNull($resource->getMimeType());
        $this->assertNull($resource->getSize());
        $this->assertEquals('', $resource->getServer());
        $this->assertTrue($resource->isEnabled());
        $this->assertFalse($resource->isTemplate());
        $this->assertEquals([], $resource->getUriTemplate());
    }

    /**
     * @dataProvider validResourceNamesProvider
     */
    public function testValidResourceNames(string $name): void
    {
        $resource = new Resource(name: $name);
        $this->assertEquals($name, $resource->getName());
    }

    public static function validResourceNamesProvider(): array
    {
        return [
            ['valid_name'],
            ['valid-name'],
            ['ValidName'],
            ['resource123'],
            ['RESOURCE_NAME'],
            ['resource-name-123'],
            ['resource_name_123'],
        ];
    }

    /**
     * @dataProvider invalidResourceNamesProvider
     */
    public function testInvalidResourceNames(string $name): void
    {
        $this->expectException(ToolError::class);
        new Resource(name: $name);
    }

    public static function invalidResourceNamesProvider(): array
    {
        return [
            ['invalid name'],
            ['invalid.name'],
            ['invalid@name'],
            ['invalid#name'],
            ['invalid/name'],
        ];
    }

    /**
     * @dataProvider validUrisProvider
     */
    public function testValidUris(string $uri): void
    {
        $resource = new Resource(uri: $uri);
        $this->assertEquals($uri, $resource->getUri());
    }

    public static function validUrisProvider(): array
    {
        return [
            ['http://example.com'],
            ['https://example.com/path'],
            ['mcp://test/resource'],
            ['file:///path/to/file'],
            ['custom-scheme://resource'],
        ];
    }

    /**
     * @dataProvider invalidUrisProvider
     */
    public function testInvalidUris(string $uri): void
    {
        $this->expectException(ToolError::class);
        new Resource(uri: $uri);
    }

    public static function invalidUrisProvider(): array
    {
        return [
            ['://missing-scheme'],
            ['ht tp://invalid spaces'],  // space in scheme
        ];
    }
}
