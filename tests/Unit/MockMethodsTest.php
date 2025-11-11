<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Unit;

use Chubbyphp\Mock\Exceptions\AdditionalMethodMocks;
use Chubbyphp\Mock\Exceptions\MissingMockMethod;
use Chubbyphp\Mock\MockMethod\WithCallback;
use Chubbyphp\Mock\MockMethod\WithException;
use Chubbyphp\Mock\MockMethod\WithoutReturn;
use Chubbyphp\Mock\MockMethod\WithReturn;
use Chubbyphp\Mock\MockMethod\WithReturnSelf;
use Chubbyphp\Mock\MockMethods;
use PHPUnit\Framework\TestCase;

final class MockMethodsSample
{
    public function withCallback(string $parameter): string
    {
        return $parameter;
    }

    public function withException(string $parameter): string
    {
        throw new \Exception('message');
    }

    public function withReturn(string $parameter): string
    {
        return $parameter;
    }

    public function withReturnSelf(string $parameter): self
    {
        return $this;
    }

    public function withoutReturn(string $parameter): void {}
}

/**
 * @covers \Chubbyphp\Mock\MockMethods
 *
 * @internal
 */
final class MockMethodsTest extends TestCase
{
    public function testWithoutMethodMocksWithCalls(): void
    {
        $object = new MockMethodsSample();

        $mockMethods = new MockMethods('in', 'class', []);

        try {
            $mockMethods->mock($object, 'withReturn', ['parameter']);

            throw new \Exception('should not be reachable');
        } catch (MissingMockMethod $e) {
            $expectedMessage = <<<'EOT'
                {
                    "exception": "Chubbyphp\\Mock\\Exceptions\\MissingMockMethod",
                    "in": "in",
                    "class": "class",
                    "index": 0
                }
                EOT;
            self::assertSame($expectedMessage, $e->getMessage());
        }
    }

    public function testWithMethodMocksWithoutCalls(): void
    {
        $mockMethods = new MockMethods('in', 'class', [
            new WithReturn('withReturn', ['parameter'], 'parameter'),
        ]);

        try {
            unset($mockMethods);

            throw new \Exception('should not be reachable');
        } catch (AdditionalMethodMocks $e) {
            $expectedMessage = <<<'EOT'
                {
                    "exception": "Chubbyphp\\Mock\\Exceptions\\AdditionalMethodMocks",
                    "in": "in",
                    "class": "class",
                    "actualIndex": -1,
                    "expectedIndex": 0
                }
                EOT;
            self::assertSame($expectedMessage, $e->getMessage());
        }
    }

    public function testWithMethodMocksWithCalls(): void
    {
        $object = new MockMethodsSample();
        $exception = new \Exception('message');

        $mockMethods = new MockMethods('in', 'class', [
            new WithCallback('withCallback', static fn (string $parameter) => $parameter),
            new WithException('withException', ['parameter'], $exception),
            new WithReturn('withReturn', ['parameter'], 'parameter'),
            new WithReturnSelf('withReturnSelf', ['parameter']),
            new WithoutReturn('withoutReturn', ['parameter']),
        ]);

        self::assertSame('parameter', $mockMethods->mock($object, 'withCallback', ['parameter']));

        try {
            $mockMethods->mock($object, 'withException', ['parameter']);

            throw new \Exception('should not be reachable');
        } catch (\Exception $e) {
            self::assertSame($exception, $e);
        }

        self::assertSame('parameter', $mockMethods->mock($object, 'withReturn', ['parameter']));

        self::assertSame($object, $mockMethods->mock($object, 'withReturnSelf', ['parameter']));

        $mockMethods->mock($object, 'withoutReturn', ['parameter']);
    }
}
