<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Unit\Exceptions;

use Chubbyphp\Mock\Exceptions\ParameterMismatch;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Mock\Exceptions\ParameterMismatch
 *
 * @internal
 */
final class ParameterMismatchTest extends TestCase
{
    public function testConstruct(): void
    {
        $message = <<<'EOT'
            {
                "exception": "Chubbyphp\\Mock\\Exceptions\\ParameterMismatch",
                "in": "in",
                "class": "class",
                "index": 2,
                "methodName": "methodName",
                "parameterIndex": 1,
                "actualParameter": "actualParameter",
                "expectedParameter": "expectedParameter",
                "strict": true
            }
            EOT;

        $exception = new ParameterMismatch(
            'in',
            'class',
            2,
            'methodName',
            1,
            'actualParameter',
            'expectedParameter',
            true,
        );

        self::assertSame($message, $exception->getMessage());
        self::assertSame(41273, $exception->getCode());
    }
}
