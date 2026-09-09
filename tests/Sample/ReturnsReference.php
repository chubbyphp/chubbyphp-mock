<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Sample;

class ReturnsReference
{
    private static string $staticValue = 'staticValue';

    private string $value = 'value';

    public function &getValue(): string
    {
        return $this->value;
    }

    public static function &getStaticValue(): string
    {
        return self::$staticValue;
    }
}
