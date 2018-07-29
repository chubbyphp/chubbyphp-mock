<?php

declare(strict_types=1);

namespace Chubbyphp\Mock\Argument;

class ArgumentCallback implements ArgumentInterface
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @param mixed $argument
     * @param array $context
     */
    public function assert($argument, array $context)
    {
        $callback = $this->callback;
        $callback($argument, $context);
    }
}
