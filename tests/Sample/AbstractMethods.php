<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Sample;

abstract class AbstractMethods
{
    public function toLower(string $text): string
    {
        return $this->internalToLower($text);
    }

    abstract protected function internalToLower(string $text): string;
}
