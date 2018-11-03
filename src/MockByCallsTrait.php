<?php

declare(strict_types=1);

namespace Chubbyphp\Mock;

use Chubbyphp\Mock\Argument\ArgumentInterface;
use PHPUnit\Framework\MockObject\MockObject;

trait MockByCallsTrait
{
    /**
     * @param string[]|string $class
     * @param Call[]          $calls
     *
     * @return MockObject
     */
    private function getMockByCalls($class, array $calls = []): MockObject
    {
        $mockBuilder = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->disableOriginalClone();

        $mock = $mockBuilder->getMock();

        $mockName = (new \ReflectionObject($mock))->getShortName();

        $class = $this->getMockClassAsString($class);

        $options = JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

        $callIndex = -1;

        $mock->expects(self::any())->method(self::anything())->willReturnCallback(
            function () use ($class, $mock, $mockName, $callIndex, &$calls, $options) {
                ++$callIndex;
                $call = array_shift($calls);

                if (!$call instanceof Call) {
                    self::fail(
                        // fixme: $callIndex + 1 is logically wrong ...
                        sprintf('Additional call at index %d on class "%s"', $callIndex + 1, $class)
                        .PHP_EOL
                        .json_encode($this->getStackTrace($mock), $options)
                    );
                }

                $mocketMethod = $this->getMockedMethod($mockName);

                self::assertSame(
                    $mocketMethod,
                    $call->getMethod(),
                    sprintf(
                        'Call at index %d on class "%s" expected method "%s", "%s" given',
                        $callIndex,
                        $class,
                        $call->getMethod(),
                        $mocketMethod
                    ).PHP_EOL.json_encode($this->getStackTrace($mock), $options)
                );

                return $this->getMockCallback($class, $callIndex, $call, $mock)(...func_get_args());
            }
        );

        return $mock;
    }

    /**
     * @param string[]|string $class
     *
     * @return string
     */
    private function getMockClassAsString($class): string
    {
        if (is_array($class)) {
            return implode('|', $class);
        }

        return $class;
    }

    /**
     * @param string $mockName
     *
     * @return string
     */
    private function getMockedMethod(string $mockName): string
    {
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $trace) {
            if ($mockName === $trace['class']) {
                return $trace['function'];
            }
        }
    }

    /**
     * @param string     $class
     * @param int        $callIndex
     * @param Call       $call
     * @param MockObject $mock
     *
     * @return \Closure
     */
    private function getMockCallback(
        string $class,
        int $callIndex,
        Call $call,
        MockObject $mock
    ): \Closure {
        return function () use ($class, $callIndex, $call, $mock) {
            if ($call->hasWith()) {
                $this->compareArguments($class, $call->getMethod(), $callIndex, $call->getWith(), func_get_args());
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
