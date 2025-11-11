<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Unit\Exceptions;

use Chubbyphp\Mock\Exceptions\ParametersCountMismatch;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Mock\Exceptions\ParametersCountMismatch
 *
 * @internal
 */
final class ParametersCountMismatchTest extends TestCase
{
    public function testConstruct(): void
    {
        $message = <<<'EOT'
            {
                "exception": "Chubbyphp\\Mock\\Exceptions\\ParametersCountMismatch",
                "in": "in",
                "class": "class",
                "index": 4,
                "methodName": "methodName",
                "actualParametersCount": 4,
                "expectedParametersCount": 5
            }
            EOT;

        $exception = new ParametersCountMismatch(
            'in',
            'class',
            4,
            'methodName',
            4,
            5,
            true,
        );

        self::assertSame($message, $exception->getMessage());
        self::assertSame(20470, $exception->getCode());
    }
}
