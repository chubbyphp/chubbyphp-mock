<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Sample;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class MethodAttribute
{
    public function __construct(public string $name) {}
}
