<?php

declare(strict_types=1);

namespace Chubbyphp\Mock\Argument;

use PHPUnit\Framework\Assert;

class ArgumentInstanceOf implements ArgumentInterface
{
    /**
     * @var string
     */
    private $class;

    /**
     * @param string $class
     */
    public function __construct(string $class)
    {
        $this->class = $class;
    }

    /**
     * @param mixed  $argument
     * @param string $message
     */
    public function assert($argument, string $message)
    {
        Assert::assertInstanceOf($this->class, $argument, $message);
    }
}
