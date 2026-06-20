<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Sample;

interface IteratorInterface extends \Iterator
{
    public function count(): int;
}
