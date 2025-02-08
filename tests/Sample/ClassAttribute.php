<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Sample;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class ClassAttribute
{
    public function __construct(public string $name) {}
}
