<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Sample;

class MixedParameters
{
    public function withoutParameters(): void {}

    public function withParameters(string $typed, $untyped, string &$byReference, int $default = 10, string ...$variadic): ?self
    {
        return $this;
    }

    public static function staticWithParameters(string $typed, int $default = 10): string
    {
        return $typed.$default;
    }
}
