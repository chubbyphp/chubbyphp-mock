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

        foreach ($calls as $at => $call) {
            $mock->expects(self::at($at))
                ->method($call->getMethod())
                ->willReturnCallback($this->getMockCallback($class, $at, $call, $mock));
        }

        $callIndex = 0;

        $mock->expects(self::any())->method(self::anything())->willReturnCallback(
            function () use ($class, $mock, $callCount, &$callIndex) {
                if ($callIndex === $callCount) {
                    $options = JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

                    self::fail(
                        sprintf('Additional call at index %d on class "%s"', $callIndex, $class)
                        .PHP_EOL
                        .json_encode($this->getStackTrace($mock), JSON_PRETTY_PRINT | $options)
                    );
                }

                ++$callIndex;
            }
        );

        return $mock;
    }

    /**
     * @param string     $class
     * @param int        $at
     * @param Call       $call
     * @param MockObject $mock
     *
     * @return \Closure
     */
    private function getMockCallback(
        string $class,
        int $at,
        Call $call,
        MockObject $mock
    ): \Closure {
        return function () use ($class, $at, $call, $mock) {
            if ($call->hasWith()) {
                $this->compareArguments($class, $call->getMethod(), $at, $call->getWith(), func_get_args());
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
     * @param int    $at
     * @param array  $expectedArguments
     * @param array  $arguments
     */
    private function compareArguments(
        string $class,
        string $method,
        int $at,
        array $expectedArguments,
        array $arguments
    ) {
        $expectedArgumentsCount = count($expectedArguments);
        $argumentsCount = count($arguments);

        self::assertSame(
            $expectedArgumentsCount,
            $argumentsCount,
            sprintf(
                'Method "%s" on class "%s" at call %d, got %d arguments, but %d are expected',
                $method,
                $class,
                $at,
                $expectedArgumentsCount,
                $argumentsCount
            )
        );

        foreach ($expectedArguments as $index => $expectedArgument) {
            if ($expectedArgument instanceof ArgumentInterface) {
                $expectedArgument->assert(
                    $arguments[$index],
                    ['class' => $class, 'method' => $method, 'at' => $at, 'index' => $index]
                );

                continue;
            }

            self::assertSame(
                $expectedArgument,
                $arguments[$index],
                sprintf(
                    'Method "%s" on class "%s" at call %d, argument %d',
                    $method,
                    $class,
                    $at,
                    $index
                )
            );
        }
    }

    /**
     * @param MockObject $mock
     *
     * @return array
     */
    private function getStackTrace(MockObject $mock): array
    {
        $mockName = (new \ReflectionObject($mock))->getShortName();

        $trace = [];
        $enableTrace = false;
        foreach (debug_backtrace() as $i => $row) {
            if (isset($row['class']) && $mockName === $row['class']) {
                $enableTrace = true;
            }

            if ($enableTrace) {
                $traceRow = $row['class'].$row['type'].$row['function'];

                if (isset($row['file'])) {
                    $traceRow .= sprintf(' (%s:%d)', $row['file'], $row['line']);
                }

                $trace[] = $traceRow;
            }
        }

        krsort($trace);

        return array_values($trace);
    }
}
