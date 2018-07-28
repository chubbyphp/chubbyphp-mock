<?php

declare(strict_types=1);

namespace Chubbyphp\Mock;

use Chubbyphp\Mock\Argument\ArgumentInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

trait MockByCallsTrait
{
    /**
     * @param string $class
     * @param Call[] $calls
     *
     * @return MockObject
     */
    private function getMockByCalls(string $class, array $calls = []): MockObject
    {
        /** @var MockByCallsTrait|TestCase $this */
        $reflectionClass = new \ReflectionClass($class);

        $mockBuilder = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->disableOriginalClone();

        /* @var MockObject $mock */
        if ($reflectionClass->isAbstract() || $reflectionClass->isInterface()) {
            $mock = $mockBuilder->getMockForAbstractClass();
        } else {
            $mock = $mockBuilder->getMock();
        }

        $callCount = count($calls);

        if (0 === $callCount) {
            $mock->expects(self::never())->method(self::anything());

            return $mock;
        }

        foreach ($calls as $i => $call) {
            $mock->expects(self::at($i))
                ->method($call->getMethod())
                ->willReturnCallback($this->getMockCallback($class, $i, $call, $mock));
        }

        $callIndex = 0;

        $mock->expects(self::any())->method(self::anything())->willReturnCallback(
            function () use ($class, $callCount, &$callIndex) {
                if ($callIndex === $callCount) {
                    self::fail(sprintf('Additional call at index %d on class "%s"!', $callIndex, $class));
                }

                ++$callIndex;
            }
        );

        return $mock;
    }

    /**
     * @param string     $class
     * @param int        $i
     * @param Call       $call
     * @param MockObject $mock
     *
     * @return \Closure
     */
    private function getMockCallback(
        string $class,
        int $i,
        Call $call,
        MockObject $mock
    ): \Closure {
        return function () use ($class, $i, $call, $mock) {
            if ($call->hasWith()) {
                $this->compareArguments($class, $call->getMethod(), $i, $call->getWith(), func_get_args());
            }

            if (null !== $exception = $call->getException()) {
                throw $exception;
            }

            if ($call->hasReturnSelf()) {
                return $mock;
            }

            if ($call->hasReturn()) {
                return $call->getReturn();
            }
        };
    }

    /**
     * @param string $class
     * @param string $method
     * @param int    $i
     * @param array  $expectedArguments
     * @param array  $arguments
     */
    private function compareArguments(
        string $class,
        string $method,
        int $i,
        array $expectedArguments,
        array $arguments
    ) {
        $expectedArgumentsCount = count($expectedArguments);
        $argumentsCount = count($arguments);

        self::assertSame(
            $expectedArgumentsCount,
            $argumentsCount,
            sprintf(
                'Method "%s" on class "%s" at call with index %d, got %d arguments, but %d are expected',
                $method,
                $class,
                $i,
                $expectedArgumentsCount,
                $argumentsCount
            )
        );

        foreach ($expectedArguments as $y => $expectedArgument) {
            if ($expectedArgument instanceof ArgumentInterface) {
                $expectedArgument->assert(
                    $arguments[$y],
                    sprintf('Method "%s" on class "%s" at call with index %d', $method, $class, $i)
                );

                continue;
            }

            self::assertSame(
                $expectedArgument,
                $arguments[$y],
                sprintf('Method "%s" on class "%s" at call with index %d', $method, $class, $i)
            );
        }
    }
}
