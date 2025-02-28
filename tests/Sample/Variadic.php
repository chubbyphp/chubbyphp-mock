<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Sample;

class Variadic
{
    public function join(string $separator, ...$strings): string
    {
        return implode($separator, $strings);
    }
}
