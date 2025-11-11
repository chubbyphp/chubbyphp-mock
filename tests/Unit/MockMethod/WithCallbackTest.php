<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Unit\MockMethod;

use Chubbyphp\Mock\Exceptions\MethodNameMismatch;
use Chubbyphp\Mock\MockMethod\WithCallback;
use PHPUnit\Framework\TestCase;

class WithCallbackSample
{
    public function withCallback(string $parameter): string
    {
        return $parameter;
    }
}

/**
 * @covers \Chubbyphp\Mock\MockMethod\WithCallback
 *
 * @internal
 */
final class WithCallbackTest extends TestCase
{
    public function testMockWithoutMatchingMethodName(): void
    {
        $object = new WithCallbackSample();

        $mockMethod = new WithCallback('withCallback', static fn (string $input) => $input);

        try {
            $mockMethod->mock('in', 'class', $object, 0, 'withoutCallback', ['string']);

            throw new \Exception('should not be reachable');
        } catch (MethodNameMismatch $e) {
            $expectedMessage = <<<'EOT'
                {
                    "exception": "Chubbyphp\\Mock\\Exceptions\\MethodNameMismatch",
                    "in": "in",
                    "class": "class",
                    "index": 0,
                    "actualName": "withoutCallback",
                    "expectedName": "withCallback"
                }
                EOT;
            self::assertSame($expectedMessage, $e->getMessage());
        }
    }

    public function testMockWithMatchingMethodName(): void
    {
        $object = new WithCallbackSample();

        $mockMethod = new WithCallback('withCallback', static fn (string $input) => $input);

        self::assertSame('string', $mockMethod->mock('in', 'class', $object, 0, 'withCallback', ['string']));
    }
}
