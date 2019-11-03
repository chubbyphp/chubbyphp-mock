<?php

declare(strict_types=1);

namespace Chubbyphp\Mock\Argument;

interface ArgumentInterface
{
    /**
     * @param mixed $argument
     */
    public function assert($argument, array $context);
}
