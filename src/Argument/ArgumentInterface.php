<?php

declare(strict_types=1);

namespace Chubbyphp\Mock\Argument;

interface ArgumentInterface
{
    /**
     * @param mixed  $argument
     * @param string $message
     */
    public function assert($argument, string $message);
}
