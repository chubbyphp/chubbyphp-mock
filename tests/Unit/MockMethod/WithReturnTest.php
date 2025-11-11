<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Unit\MockMethod;

use Chubbyphp\Mock\Exceptions\MethodNameMismatch;
use Chubbyphp\Mock\Exceptions\ParameterMismatch;
use Chubbyphp\Mock\Exceptions\ParametersCountMismatch;
use Chubbyphp\Mock\MockMethod\WithReturn;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;

class WithReturnSample
{
    public function withReturn(mixed $parameter): void {}
}

/**
 * @covers \Chubbyphp\Mock\MockMethod\WithReturn
 *
 * @internal
 */
final class WithReturnTest extends AbstractHasParameter
{
    public function testMockWithoutMatchingMethodName(): void
    {
        $object = new WithReturnSample();

        $mockMethod = new WithReturn('withReturn', ['string'], 'string');

        try {
            $mockMethod->mock('in', 'class', $object, 0, 'withoutReturn', ['string']);

            throw new \Exception('should not be reachable');
        } catch (MethodNameMismatch $e) {
            $expectedMessage = <<<'EOT'
                {
                    "exception": "Chubbyphp\\Mock\\Exceptions\\MethodNameMismatch",
                    "in": "in",
                    "class": "class",
                    "index": 0,
                    "actualName": "withoutReturn",
                    "expectedName": "withReturn"
                }
                EOT;
            self::assertSame($expectedMessage, $e->getMessage());
        }
    }

    public function testMockWithoutMatchingParameterCount(): void
    {
        $object = new withReturnSample();

        $mockMethod = new WithReturn('withReturn', ['string'], 'string');

        try {
            $mockMethod->mock('in', 'class', $object, 0, 'withReturn', []);

            throw new \Exception('should not be reachable');
        } catch (ParametersCountMismatch $e) {
            $expectedMessage = <<<'EOT'
                {
                    "exception": "Chubbyphp\\Mock\\Exceptions\\ParametersCountMismatch",
                    "in": "in",
                    "class": "class",
                    "index": 0,
                    "methodName": "withReturn",
                    "actualParametersCount": 0,
                    "expectedParametersCount": 1
                }
                EOT;
            self::assertSame($expectedMessage, $e->getMessage());
        }
    }

    #[DoesNotPerformAssertions]
    #[DataProvider('provideDifferentTypeData')]
    public function testWithDifferentType(mixed $expected, mixed $actual): void
    {
        $object = new WithReturnSample();

        $mockMethod = new WithReturn('withReturn', [$expected], 'string', false);

        try {
            $mockMethod->mock('in', 'class', $object, 0, 'withReturn', [$actual]);

            throw new \Exception('should not be reachable');
        } catch (ParameterMismatch) {
        }
    }

    #[DataProvider('provideSameData')]
    public function testWithSameData(mixed $expected, mixed $actual): void
    {
        $object = new WithReturnSample();

        $mockMethod = new WithReturn('withReturn', [$expected], $expected);

        self::assertSame($expected, $mockMethod->mock('in', 'class', $object, 0, 'withReturn', [$actual]));
    }

    #[DoesNotPerformAssertions]
    #[DataProvider('provideNotSameData')]
    public function testWithNotSameData(mixed $expected, mixed $actual): void
    {
        $object = new WithReturnSample();

        $mockMethod = new WithReturn('withReturn', [$expected], $expected);

        try {
            $mockMethod->mock('in', 'class', $object, 0, 'withReturn', [$actual]);

            throw new \Exception('should not be reachable');
        } catch (ParameterMismatch) {
        }
    }

    #[DataProvider('provideEqualData')]
    public function testWithEqualData(mixed $expected, mixed $actual): void
    {
        $object = new WithReturnSample();

        $mockMethod = new WithReturn('withReturn', [$expected], $expected, false);

        self::assertSame($expected, $mockMethod->mock('in', 'class', $object, 0, 'withReturn', [$actual]));
    }

    #[DoesNotPerformAssertions]
    #[DataProvider('provideNotEqualData')]
    public function testWithNotEqualData(mixed $expected, mixed $actual): void
    {
        $object = new WithReturnSample();

        $mockMethod = new WithReturn('withReturn', [$expected], $expected, false);

        try {
            $mockMethod->mock('in', 'class', $object, 0, 'withReturn', [$actual]);

            throw new \Exception('should not be reachable');
        } catch (ParameterMismatch) {
        }
    }
}
