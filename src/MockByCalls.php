<?php

declare(strict_types=1);

namespace Chubbyphp\Mock;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class MockByCalls extends TestCase
{
    use MockByCallsTrait;
}
