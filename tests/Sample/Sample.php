<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Sample;

final class Sample
{
    public function __construct(private string $name, private string $value) {}
}
