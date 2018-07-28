<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Argument;

use Chubbyphp\Mock\Argument\ArgumentCallback;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Mock\Argument\ArgumentCallback
 */
class ArgumentCallbackTest extends TestCase
{
    public function testAssert()
    {
        $expectedArgument = 'test';
        $expectedMessage = 'message';

        $argumentCallback = new ArgumentCallback(
            function ($argument, string $message) use ($expectedArgument, $expectedMessage) {
                self::assertSame($expectedArgument, $argument);
                self::assertSame($expectedMessage, $message);
            }
        );

        $argumentCallback->assert($expectedArgument, $expectedMessage);
    }
}
