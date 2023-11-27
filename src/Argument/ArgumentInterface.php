<?php

declare(strict_types=1);

namespace Chubbyphp\Mock\Argument;

interface ArgumentInterface
{
    /**
     * @param array<mixed> $context
     */
    public function assert(mixed $argument, array $context): void;
}
