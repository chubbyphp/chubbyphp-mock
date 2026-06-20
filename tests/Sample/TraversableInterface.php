<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Sample;

interface TraversableInterface extends \Traversable
{
    public function count(): int;
}
