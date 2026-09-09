<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Sample;

abstract class AbstractMethods
{
    public function toLower(string $text): string
    {
        return $this->internalToLower($this->privateToLower($text));
    }

    abstract protected function internalToLower(string $text): string;

    abstract protected static function internalStaticToLower(string $text): string;

    private function privateToLower(string $text): string
    {
        return strtolower($text);
    }
}
