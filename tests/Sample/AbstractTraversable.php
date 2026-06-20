<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Sample;

abstract class AbstractTraversable implements \Traversable
{
    abstract public function isEmpty(): bool;
}
