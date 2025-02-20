<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Unit\MockMethod;

use Chubbyphp\Mock\Exceptions\MethodNameMismatch;
use Chubbyphp\Mock\Exceptions\ParameterMismatch;
use Chubbyphp\Mock\Exceptions\ParametersCountMismatch;
use Chubbyphp\Mock\MockMethod\WithReturnSelf;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;

class WithReturnSelfSample
{
    public function withReturnSelf(mixed $parameter): void {}
}

/**
 * @covers \Chubbyphp\Mock\MockMethod\WithReturnSelf
 *
 * @internal
 */
final class WithReturnSelfTest extends AbstractHasParameter
{
    public function testMockWithoutMatchingMethodName(): void
    {
        $object = new WithReturnSelfSample();

        $mockMethod = new WithReturnSelf('withReturnSelf', ['string']);

        try {
            $mockMethod->mock('in', 'class', $object, 0, 'withoutReturn', ['string']);

            throw new \Exception('should not be reachable');
        } catch (MethodNameMismatch $e) {
            $expectedMessage = <<<'EOT'
                {
                    "in": "in",
                    "class": "class",
                    "index": 0,
                    "actualName": "withoutReturn",
                    "expectedName": "withReturnSelf"
                }
                EOT;
            self::assertSame($expectedMessage, $e->getMessage());
        }
    }

    public function testMockWithoutMatchingParameterCount(): void
    {
        $object = new withReturnSelfSample();

        $mockMethod = new WithReturnSelf('withReturnSelf', ['string']);

        try {
            $mockMethod->mock('in', 'class', $object, 0, 'withReturnSelf', []);

            throw new \Exception('should not be reachable');
        } catch (ParametersCountMismatch $e) {
            $expectedMessage = <<<'EOT'
                {
                    "in": "in",
                    "class": "class",
                    "index": 0,
                    "methodName": "withReturnSelf",
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
        $object = new WithReturnSelfSample();

        $mockMethod = new WithReturnSelf('withReturnSelf', [$expected], false);

        try {
            $mockMethod->mock('in', 'class', $object, 0, 'withReturnSelf', [$actual]);

            throw new \Exception('should not be reachable');
        } catch (ParameterMismatch) {
        }
    }

    #[DataProvider('provideSameData')]
    public function testWithSameData(mixed $expected, mixed $actual): void
    {
        $object = new WithReturnSelfSample();

        $mockMethod = new WithReturnSelf('withReturnSelf', [$expected]);

        self::assertSame($object, $mockMethod->mock('in', 'class', $object, 0, 'withReturnSelf', [$actual]));
    }

    #[DoesNotPerformAssertions]
    #[DataProvider('provideNotSameData')]
    public function testWithNotSameData(mixed $expected, mixed $actual): void
    {
        $object = new WithReturnSelfSample();

        $mockMethod = new WithReturnSelf('withReturnSelf', [$expected]);

        try {
            $mockMethod->mock('in', 'class', $object, 0, 'withReturnSelf', [$actual]);

            throw new \Exception('should not be reachable');
        } catch (ParameterMismatch) {
        }
    }

    #[DataProvider('provideEqualData')]
    public function testWithEqualData(mixed $expected, mixed $actual): void
    {
        $object = new WithReturnSelfSample();

        $mockMethod = new WithReturnSelf('withReturnSelf', [$expected], false);

        self::assertSame($object, $mockMethod->mock('in', 'class', $object, 0, 'withReturnSelf', [$actual]));
    }

    #[DoesNotPerformAssertions]
    #[DataProvider('provideNotEqualData')]
    public function testWithNotEqualData(mixed $expected, mixed $actual): void
    {
        $object = new WithReturnSelfSample();

        $mockMethod = new WithReturnSelf('withReturnSelf', [$expected], false);

        try {
            $mockMethod->mock('in', 'class', $object, 0, 'withReturnSelf', [$actual]);

            throw new \Exception('should not be reachable');
        } catch (ParameterMismatch) {
        }
    }
}
