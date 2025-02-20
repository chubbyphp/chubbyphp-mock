<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Unit;

use Chubbyphp\Mock\MockMethod\WithoutReturn;
use Chubbyphp\Mock\MockMethod\WithReturn;
use Chubbyphp\Mock\MockObjectBuilder;
use Chubbyphp\Tests\Mock\Sample\DefaultParameters;
use Chubbyphp\Tests\Mock\Sample\Sample;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;

/**
 *  @covers \Chubbyphp\Mock\MockObjectBuilder
 *
 * @internal
 */
final class MockObjectBuilderTest extends TestCase
{
    #[DoesNotPerformAssertions]
    public function testWithDefaultParameters(): void
    {
        $builder = new MockObjectBuilder();

        /** @var DefaultParameters $defaultParameters */
        $defaultParameters = $builder->create(DefaultParameters::class, [
            new WithoutReturn('defaultParameters', [
                null,
                null,
                true,
                false,
                null,
                ZEND_THREAD_SAFE,
                true,
                42,
                null,
                PHP_INT_MIN,
                5,
                3.14159,
                null,
                PHP_FLOAT_MIN,
                9.81,
                'string',
                null,
                PHP_EOL,
                'string',
                ['null' => null, 'boolean' => true, 'int' => 5, 'float' => 9.81, 'string' => 'string'],
                null,
                new \DateTimeImmutable('2025-02-16T00:25:30+01:00', new \DateTimeZone('Europe/Zurich')),
                new \ArrayIterator(['null' => null, 'boolean' => true, 'int' => 5, 'float' => 9.81, 'string' => 'string']),
                new Sample('name', 'value'),
            ], false),
        ]);

        $defaultParameters->defaultParameters();
    }

    public function testWithDateTimeImmutable(): void
    {
        $builder = new MockObjectBuilder();

        /** @var \DateTimeImmutable $dateTimeImmutable */
        $dateTimeImmutable = $builder->create(\DateTimeImmutable::class, [
            new WithReturn('format', ['c'], '2025-02-16T00:25:30+01:00'),
        ]);

        self::assertSame('2025-02-16T00:25:30+01:00', $dateTimeImmutable->format('c'));

        /** @var \DateTimeImmutable $dateTimeImmutable */
        $dateTimeImmutable = $builder->create(\DateTimeImmutable::class, [
            new WithReturn('format', ['c'], '2025-02-20T22:22:22+01:00'),
        ]);

        self::assertSame('2025-02-20T22:22:22+01:00', $dateTimeImmutable->format('c'));
    }
}
