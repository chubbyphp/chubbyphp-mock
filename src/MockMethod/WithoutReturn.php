<?php

declare(strict_types=1);

namespace Chubbyphp\Mock\MockMethod;

use Chubbyphp\Mock\Exceptions\MethodNameMismatch;
use Chubbyphp\Mock\Exceptions\ParameterMismatch;
use Chubbyphp\Mock\Exceptions\ParametersCountMismatch;
use Chubbyphp\Mock\Utils;

final class WithoutReturn implements MockMethodInterface
{
    /**
     * @param array<mixed> $expectedParameters
     */
    public function __construct(private readonly string $expectedName, private readonly array $expectedParameters, private readonly bool $strict = true) {}

    /**
     * @param array<mixed> $actualParameters
     */
    public function mock(
        string $in,
        string $class,
        object $object,
        int $index,
        string $actualName,
        array $actualParameters,
    ): void {
        if ($actualName !== $this->expectedName) {
            throw new MethodNameMismatch(
                $in,
                $class,
                $index,
                $actualName,
                $this->expectedName
            );
        }

        $this->validateParameters(
            $in,
            $class,
            $index,
            $actualName,
            $actualParameters,
            $this->expectedParameters,
            $this->strict,
        );
    }

    /**
     * @param array<mixed> $actualParameters
     * @param array<mixed> $expectedParameters
     */
    private function validateParameters(
        string $in,
        string $class,
        int $index,
        string $actualName,
        array $actualParameters,
        array $expectedParameters,
        bool $strict,
    ): void {
        $actualParametersCount = \count($actualParameters);
        $expectedParametersCount = \count($expectedParameters);

        if ($actualParametersCount !== $expectedParametersCount) {
            throw new ParametersCountMismatch(
                $in,
                $class,
                $index,
                $actualName,
                $actualParametersCount,
                $expectedParametersCount,
            );
        }

        foreach ($expectedParameters as $parameterIndex => $expectedParameter) {
            $actualParameter = $actualParameters[$parameterIndex];

            if ($strict) {
                if ($actualParameter !== $expectedParameter) {
                    throw new ParameterMismatch(
                        $in,
                        $class,
                        $index,
                        $actualName,
                        $parameterIndex,
                        $actualParameter,
                        $expectedParameter,
                        true,
                    );
                }
            } else {
                if (!$this->compareEqual($actualParameter, $expectedParameter)) {
                    throw new ParameterMismatch(
                        $in,
                        $class,
                        $index,
                        $actualName,
                        $parameterIndex,
                        $actualParameter,
                        $expectedParameter,
                        false,
                    );
                }
            }
        }
    }

    private function compareEqual(mixed $actual, mixed $expected): bool
    {
        if ($actual === $expected) {
            return true;
        }

        if (!$this->compareSameType($actual, $expected)) {
            return false;
        }

        return $this->compareSameTypeEqual($actual, $expected);
    }

    private function compareSameTypeEqual(mixed $actual, mixed $expected): bool
    {
        if (\is_array($actual)) {
            /** @var array<mixed> $expected */
            return $this->compareArrayEqual($actual, $expected);
        }

        if (\is_object($actual)) {
            /** @var object $expected */
            return $this->compareObjectEqual($actual, $expected);
        }

        return false;
    }

    private function compareSameType(mixed $actualParameter, mixed $expectedParameter): bool
    {
        return $this->getType($actualParameter) === $this->getType($expectedParameter);
    }

    private function getType(mixed $data): string
    {
        return \is_object($data) ? $data::class : \gettype($data);
    }

    private function compareObjectEqual(object $actual, object $expected): bool
    {
        $reflectionObject = new \ReflectionObject($actual);

        foreach (['__serialize', '__sleep'] as $method) {
            if ($reflectionObject->hasMethod($method)) {
                $reflectionMethod = $reflectionObject->getMethod($method);

                $actualSerializedValue = $reflectionMethod->invoke($actual);
                $expectedSerializedValue = $reflectionMethod->invoke($expected);

                return $this->compareEqual($actualSerializedValue, $expectedSerializedValue);
            }
        }

        foreach ($reflectionObject->getProperties() as $reflectionProperty) {
            $actualPropertyValue = Utils::getPropertyValue($reflectionProperty, $actual);
            $expectedPropertyValue = Utils::getPropertyValue($reflectionProperty, $expected);

            if (!$this->compareEqual($actualPropertyValue, $expectedPropertyValue)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<mixed> $actual
     * @param array<mixed> $expected
     */
    private function compareArrayEqual(array $actual, array $expected): bool
    {
        if (\count($actual) !== \count($expected)) {
            return false;
        }

        foreach ($actual as $actualSubKey => $actualSubValue) {
            if (!\array_key_exists($actualSubKey, $expected)
                || !$this->compareEqual($actualSubValue, $expected[$actualSubKey])
            ) {
                return false;
            }
        }

        return true;
    }
}
