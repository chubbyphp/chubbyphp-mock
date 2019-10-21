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
class ArgumentCallbackTest extends TestCase
{
    public function testAssert()
    {
        $expectedArgument = 'test';
        $expectContext = ['class' => 'class', 'method' => 'method', 'at' => 0, 'index' => 0];

        $argumentCallback = new ArgumentCallback(
            function ($argument, array $context) use ($expectedArgument, $expectContext) {
                self::assertSame($expectedArgument, $argument);
                self::assertSame($expectContext, $context);
            }
        );

        $argumentCallback->assert($expectedArgument, $expectContext);
    }
}
