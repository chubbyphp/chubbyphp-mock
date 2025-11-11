<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Unit\Exceptions;

use Chubbyphp\Mock\Exceptions\AdditionalMethodMocks;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Mock\Exceptions\AdditionalMethodMocks
 *
 * @internal
 */
final class AdditionalMethodMocksTest extends TestCase
{
    public function testConstruct(): void
    {
        $message = <<<'EOT'
            {
                "exception": "Chubbyphp\\Mock\\Exceptions\\AdditionalMethodMocks",
                "in": "in",
                "class": "class",
                "actualIndex": 2,
                "expectedIndex": 1
            }
            EOT;

        $exception = new AdditionalMethodMocks(
            'in',
            'class',
            2,
            1
        );

        self::assertSame($message, $exception->getMessage());
        self::assertSame(90952, $exception->getCode());
    }
}
