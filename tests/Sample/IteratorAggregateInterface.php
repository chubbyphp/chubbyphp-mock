<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Sample;

interface IteratorAggregateInterface extends \IteratorAggregate
{
    public function count(): int;
}
