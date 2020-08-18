<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Unit\Argument;

use Chubbyphp\Mock\Argument\ArgumentCallback;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Mock\Argument\ArgumentCallback
 *
 * @internal
 */
final class ArgumentCallbackTest extends TestCase
{
    public function testAssert(): void
    {
        $expectedArgument = 'test';
        $expectContext = ['class' => 'class', 'method' => 'method', 'at' => 0, 'index' => 0];
        $called = false;

        $argumentCallback = new ArgumentCallback(
            function ($argument, array $context) use ($expectedArgument, $expectContext, &$called): void {
                self::assertSame($expectedArgument, $argument);
                self::assertSame($expectContext, $context);

                $called = true;
            }
        );

        $argumentCallback->assert($expectedArgument, $expectContext);

        self::assertTrue($called);
    }
}
