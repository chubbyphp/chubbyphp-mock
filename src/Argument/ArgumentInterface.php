<?php

declare(strict_types=1);

namespace Chubbyphp\Mock\Argument;

interface ArgumentInterface
{
    /**
     * @param mixed        $argument
     * @param array<mixed> $context
     */
    public function assert($argument, array $context): void;
}
