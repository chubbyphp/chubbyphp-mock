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
     * @param mixed $argument
     * @param array $context
     */
    public function assert($argument, array $context)
    {
        Assert::assertInstanceOf(
            $this->class,
            $argument,
            sprintf(
                'Method "%s" on class "%s" at call %d, argument %d',
                $context['method'],
                $context['class'],
                $context['at'],
                $context['index']
            )
        );
    }
}
