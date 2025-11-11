<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Unit\Exceptions;

use Chubbyphp\Mock\Exceptions\MethodNameMismatch;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Mock\Exceptions\MethodNameMismatch
 *
 * @internal
 */
final class MethodNameMismatchTest extends TestCase
{
    public function testConstruct(): void
    {
        $message = <<<'EOT'
            {
                "exception": "Chubbyphp\\Mock\\Exceptions\\MethodNameMismatch",
                "in": "in",
                "class": "class",
                "index": 0,
                "actualName": "actualName",
                "expectedName": "expectedName"
            }
            EOT;

        $exception = new MethodNameMismatch(
            'in',
            'class',
            0,
            'actualName',
            'expectedName',
        );

        self::assertSame($message, $exception->getMessage());
        self::assertSame(98990, $exception->getCode());
    }
}
