<?php

declare(strict_types=1);

namespace Chubbyphp\Mock\Argument;

use PHPUnit\Framework\Assert;

class ArgumentInstanceOf implements ArgumentInterface
{
    private string $class;

    public function __construct(string $class)
    {
        $this->class = $class;
    }

    /**
     * @param array<mixed> $context
     */
    public function assert(mixed $argument, array $context): void
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
