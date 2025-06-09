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

use Hyperf\Di\Annotation\AbstractAnnotation;

abstract class McpAnnotation extends AbstractAnnotation
{
    protected string $class;

    protected string $method;

    public function collectMethod(string $className, ?string $target): void
    {
        $this->class = $className;
        $this->method = $target;
        parent::collectMethod($className, $target);
    }
}
