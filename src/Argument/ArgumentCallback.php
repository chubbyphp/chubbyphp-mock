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
     * @param mixed  $argument
     * @param string $message
     */
    public function assert($argument, string $message)
    {
        $callback = $this->callback;
        $callback($argument, $message);
    }
}
