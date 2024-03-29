<?php

declare(strict_types=1);

namespace Chubbyphp\Mock\Argument;

class ArgumentCallback implements ArgumentInterface
{
    /**
     * @var callable
     */
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @param array<mixed> $context
     */
    public function assert(mixed $argument, array $context): void
    {
        ($this->callback)($argument, $context);
    }
}
