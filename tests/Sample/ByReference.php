<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Sample;

class ByReference
{
    public function toLower(string &$text): self
    {
        $text = strtolower($text);

        return $this;
    }
}
