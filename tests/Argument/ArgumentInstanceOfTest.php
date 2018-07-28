<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Argument;

use Chubbyphp\Mock\Argument\ArgumentInstanceOf;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Mock\Argument\ArgumentInstanceOf
 */
class ArgumentInstanceOfTest extends TestCase
{
    public function testAssert()
    {
        $expectedArgument = new \stdClass();
        $expectedMessage = 'message';

        $argumentCallback = new ArgumentInstanceOf(\stdClass::class);
        $argumentCallback->assert($expectedArgument, $expectedMessage);
    }

    public function testAssertFail()
    {
        $expectedArgument = new \DateTime('2004-02-12T15:19:21+00:00');
        $expectedMessage = 'message';

        $argumentCallback = new ArgumentInstanceOf(\stdClass::class);

        try {
            $argumentCallback->assert($expectedArgument, $expectedMessage);
        } catch (ExpectationFailedException $e) {
            self::assertSame(
                'message'.PHP_EOL.
                'Failed asserting that DateTime Object (...) is an instance of class "stdClass".',
                $e->getMessage()
            );

            return;
        }

        self::fail(sprintf('Expected "%s"', ExpectationFailedException::class));
    }
}
