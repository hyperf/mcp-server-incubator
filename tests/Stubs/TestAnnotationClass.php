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

namespace HyperfTest\McpServer\Stubs;

class TestAnnotationClass
{
    public function testMethod(string $param1, int $param2 = 10): string
    {
        return "test result: {$param1}, {$param2}";
    }

    public function anotherMethod(): array
    {
        return ['result' => 'data'];
    }
}
