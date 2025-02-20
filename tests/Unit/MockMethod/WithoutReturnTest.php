<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Unit\MockMethod;

use Chubbyphp\Mock\Exceptions\MethodNameMismatch;
use Chubbyphp\Mock\Exceptions\ParameterMismatch;
use Chubbyphp\Mock\Exceptions\ParametersCountMismatch;
use Chubbyphp\Mock\MockMethod\WithoutReturn;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;

class WithoutReturnSample
{
    public function withoutReturn(mixed $parameter): void {}
}

/**
 * @covers \Chubbyphp\Mock\MockMethod\WithoutReturn
 *
 * @internal
 */
final class WithoutReturnTest extends AbstractHasParameter
{
    public function testMockWithoutMatchingMethodName(): void
    {
        $object = new WithoutReturnSample();

        $mockMethod = new WithoutReturn('withoutReturn', ['string']);

        try {
            $mockMethod->mock('in', 'class', $object, 0, 'withReturn', ['string']);

            throw new \Exception('should not be reachable');
        } catch (MethodNameMismatch $e) {
            $expectedMessage = <<<'EOT'
                {
                    "in": "in",
                    "class": "class",
                    "index": 0,
                    "actualName": "withReturn",
                    "expectedName": "withoutReturn"
                }
                EOT;
            self::assertSame($expectedMessage, $e->getMessage());
        }
    }

    public function testMockWithoutMatchingParameterCount(): void
    {
        $object = new WithoutReturnSample();

        $mockMethod = new WithoutReturn('withoutReturn', ['string']);

        try {
            $mockMethod->mock('in', 'class', $object, 0, 'withoutReturn', []);

            throw new \Exception('should not be reachable');
        } catch (ParametersCountMismatch $e) {
            $expectedMessage = <<<'EOT'
                {
                    "in": "in",
                    "class": "class",
                    "index": 0,
                    "methodName": "withoutReturn",
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
        $object = new WithoutReturnSample();

        $mockMethod = new WithoutReturn('withoutReturn', [$expected], false);

        try {
            $mockMethod->mock('in', 'class', $object, 0, 'withoutReturn', [$actual]);

            throw new \Exception('should not be reachable');
        } catch (ParameterMismatch) {
        }
    }

    #[DoesNotPerformAssertions]
    #[DataProvider('provideSameData')]
    public function testWithSameData(mixed $expected, mixed $actual): void
    {
        $object = new WithoutReturnSample();

        $mockMethod = new WithoutReturn('withoutReturn', [$expected]);
        $mockMethod->mock('in', 'class', $object, 0, 'withoutReturn', [$actual]);
    }

    #[DoesNotPerformAssertions]
    #[DataProvider('provideNotSameData')]
    public function testWithNotSameData(mixed $expected, mixed $actual): void
    {
        $object = new WithoutReturnSample();

        $mockMethod = new WithoutReturn('withoutReturn', [$expected]);

        try {
            $mockMethod->mock('in', 'class', $object, 0, 'withoutReturn', [$actual]);

            throw new \Exception('should not be reachable');
        } catch (ParameterMismatch) {
        }
    }

    #[DoesNotPerformAssertions]
    #[DataProvider('provideEqualData')]
    public function testWithEqualData(mixed $expected, mixed $actual): void
    {
        $object = new WithoutReturnSample();

        $mockMethod = new WithoutReturn('withoutReturn', [$expected], false);
        $mockMethod->mock('in', 'class', $object, 0, 'withoutReturn', [$actual]);
    }

    #[DoesNotPerformAssertions]
    #[DataProvider('provideNotEqualData')]
    public function testWithNotEqualData(mixed $expected, mixed $actual): void
    {
        $object = new WithoutReturnSample();

        $mockMethod = new WithoutReturn('withoutReturn', [$expected], false);

        try {
            $mockMethod->mock('in', 'class', $object, 0, 'withoutReturn', [$actual]);

            throw new \Exception('should not be reachable');
        } catch (ParameterMismatch) {
        }
    }
}
