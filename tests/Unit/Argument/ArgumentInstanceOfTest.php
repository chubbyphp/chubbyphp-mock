<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Unit\Argument;

use Chubbyphp\Mock\Argument\ArgumentInstanceOf;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Mock\Argument\ArgumentInstanceOf
 *
 * @internal
 */
class ArgumentInstanceOfTest extends TestCase
{
    public function testAssert()
    {
        $expectedArgument = new \stdClass();
        $expectContext = ['class' => 'class', 'method' => 'method', 'at' => 0, 'index' => 0];

        $argumentCallback = new ArgumentInstanceOf(\stdClass::class);
        $argumentCallback->assert($expectedArgument, $expectContext);
    }

    public function testAssertFail()
    {
        $expectedArgument = new \DateTime('2004-02-12T15:19:21+00:00');
        $expectContext = ['class' => 'class', 'method' => 'method', 'at' => 0, 'index' => 0];

        $argumentCallback = new ArgumentInstanceOf(\stdClass::class);

        try {
            $argumentCallback->assert($expectedArgument, $expectContext);
        } catch (ExpectationFailedException $e) {
            self::assertSame(
                'Method "method" on class "class" at call 0, argument 0'.PHP_EOL.
                'Failed asserting that DateTime Object (...) is an instance of class "stdClass".',
                $e->getMessage()
            );

            return;
        }

        self::fail(sprintf('Expected "%s"', ExpectationFailedException::class));
    }
}
