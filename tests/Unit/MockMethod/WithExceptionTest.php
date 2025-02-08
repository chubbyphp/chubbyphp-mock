<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Unit\MockMethod;

use Chubbyphp\Mock\Exceptions\MethodNameMismatch;
use Chubbyphp\Mock\Exceptions\ParameterMismatch;
use Chubbyphp\Mock\Exceptions\ParametersCountMismatch;
use Chubbyphp\Mock\MockMethod\WithException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;

class WithExceptionSample
{
    public function withException(mixed $parameter): void {}
}

/**
 * @covers \Chubbyphp\Mock\MockMethod\WithException
 *
 * @internal
 */
final class WithExceptionTest extends AbstractHasParameter
{
    public function testMockWithoutMatchingMethodName(): void
    {
        $object = new WithExceptionSample();
        $exception = new \Exception('message');

        $mockMethod = new WithException('withException', ['string'], $exception);

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
                    "expectedName": "withException"
                }
                EOT;
            self::assertSame($expectedMessage, $e->getMessage());
        }
    }

    public function testMockWithoutMatchingParameterCount(): void
    {
        $object = new withExceptionSample();
        $exception = new \Exception('message');

        $mockMethod = new WithException('withException', ['string'], $exception);

        try {
            $mockMethod->mock('in', 'class', $object, 0, 'withException', []);

            throw new \Exception('should not be reachable');
        } catch (ParametersCountMismatch $e) {
            $expectedMessage = <<<'EOT'
                {
                    "in": "in",
                    "class": "class",
                    "index": 0,
                    "methodName": "withException",
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
        $object = new WithExceptionSample();
        $exception = new \Exception('message');

        $mockMethod = new WithException('withException', [$expected], $exception, false);

        try {
            $mockMethod->mock('in', 'class', $object, 0, 'withException', [$actual]);

            throw new \Exception('should not be reachable');
        } catch (ParameterMismatch) {
        }
    }

    #[DataProvider('provideSameData')]
    public function testWithSameData(mixed $expected, mixed $actual): void
    {
        $object = new WithExceptionSample();
        $exception = new \Exception('message');

        $mockMethod = new WithException('withException', [$expected], $exception);

        try {
            $mockMethod->mock('in', 'class', $object, 0, 'withException', [$actual]);

            throw new \Exception('should not be reachable');
        } catch (\Exception $e) {
            self::assertSame($exception, $e);
        }
    }

    #[DoesNotPerformAssertions]
    #[DataProvider('provideNotSameData')]
    public function testWithNotSameData(mixed $expected, mixed $actual): void
    {
        $object = new WithExceptionSample();
        $exception = new \Exception('message');

        $mockMethod = new WithException('withException', [$expected], $exception);

        try {
            $mockMethod->mock('in', 'class', $object, 0, 'withException', [$actual]);

            throw new \Exception('should not be reachable');
        } catch (ParameterMismatch) {
        }
    }

    #[DataProvider('provideEqualData')]
    public function testWithEqualData(mixed $expected, mixed $actual): void
    {
        $object = new WithExceptionSample();
        $exception = new \Exception('message');

        $mockMethod = new WithException('withException', [$expected], $exception, false);

        try {
            $mockMethod->mock('in', 'class', $object, 0, 'withException', [$actual]);

            throw new \Exception('should not be reachable');
        } catch (\Exception $e) {
            self::assertSame($exception, $e);
        }
    }

    #[DoesNotPerformAssertions]
    #[DataProvider('provideNotEqualData')]
    public function testWithNotEqualData(mixed $expected, mixed $actual): void
    {
        $object = new WithExceptionSample();
        $exception = new \Exception('message');

        $mockMethod = new WithException('withException', [$expected], $exception, false);

        try {
            $mockMethod->mock('in', 'class', $object, 0, 'withException', [$actual]);

            throw new \Exception('should not be reachable');
        } catch (ParameterMismatch) {
        }
    }
}
