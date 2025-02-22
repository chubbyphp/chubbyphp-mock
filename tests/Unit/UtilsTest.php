<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Unit;

use Chubbyphp\Mock\Utils;
use PHPUnit\Framework\TestCase;

/**
 *  @covers \Chubbyphp\Mock\Utils
 *
 * @internal
 */
final class UtilsTest extends TestCase
{
    public function testBla(): void
    {
        self::assertSame('(project)/path/to/file.php', Utils::replaceProjectInPath(getcwd().'/path/to/file.php'));
    }
}
