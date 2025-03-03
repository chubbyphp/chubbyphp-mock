<?php

declare(strict_types=1);

namespace Chubbyphp\Mock\MockMethod;

use Chubbyphp\Mock\Exceptions\MethodNameMismatch;
use Chubbyphp\Mock\Exceptions\ParameterMismatch;
use Chubbyphp\Mock\Exceptions\ParametersCountMismatch;

final class WithoutReturn implements MockMethodInterface
{
    /**
     * @param array<mixed> $expectedParameters
     * @param mixed        $strict
     */
    public function __construct(private string $expectedName, private array $expectedParameters, private $strict = true) {}

    /**
     * @param array<mixed> $actualParameters
     */
    public function mock(
        string $in,
        string $class,
        object $object,
        int $Index,
        string $actualName,
        array $actualParameters,
    ): void {
        if ($actualName !== $this->expectedName) {
            throw new MethodNameMismatch(
                $in,
                $class,
                $Index,
                $actualName,
                $this->expectedName
            );
        }

        $this->validateParameters(
            $in,
            $class,
            $Index,
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
        int $Index,
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
                $Index,
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
                        $Index,
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
                        $Index,
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

        if (\is_array($actual)) {
            return $this->compareArrayEqual($actual, $expected);
        }

        if (\is_object($actual)) {
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
            $reflectionProperty->setAccessible(true);

            $actualPropertyValue = $reflectionProperty->isInitialized($actual)
                ? $reflectionProperty->getValue($actual) : '(uninitialized)';
            $expectedPropertyValue = $reflectionProperty->isInitialized($expected)
                ? $reflectionProperty->getValue($expected) : '(uninitialized)';

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
            if (!\array_key_exists($actualSubKey, $expected)) {
                return false;
            }

            $expectedSubValue = $expected[$actualSubKey];

            if (!$this->compareEqual($actualSubValue, $expectedSubValue)) {
                return false;
            }
        }

        return true;
    }
}
