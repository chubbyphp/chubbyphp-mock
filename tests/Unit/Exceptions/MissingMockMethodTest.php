<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Unit\Exceptions;

use Chubbyphp\Mock\Exceptions\MissingMockMethod;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Mock\Exceptions\MissingMockMethod
 *
 * @internal
 */
final class MissingMockMethodTest extends TestCase
{
    public function testConstruct(): void
    {
        $message = <<<'EOT'
            {
                "in": "in",
                "class": "class",
                "index": 5
            }
            EOT;

        $exception = new MissingMockMethod(
            'in',
            'class',
            5,
        );

        self::assertSame($message, $exception->getMessage());
        self::assertSame(47412, $exception->getCode());
    }
}
