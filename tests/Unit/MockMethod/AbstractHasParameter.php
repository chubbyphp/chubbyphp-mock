<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Unit\MockMethod;

use Chubbyphp\Tests\Mock\Sample\Sample;
use PHPUnit\Framework\TestCase;

abstract class AbstractHasParameter extends TestCase
{
    final public static function provideDifferentTypeData(): array
    {
        $falsy = 0;
        $truthy = 0;

        return [
            'falsy '.++$falsy => [null, false],
            'falsy '.++$falsy => [null, 0],
            'falsy '.++$falsy => [null, 0.0],
            'falsy '.++$falsy => [null, ''],
            'falsy '.++$falsy => [false, null],
            'falsy '.++$falsy => [false, 0],
            'falsy '.++$falsy => [false, 0.0],
            'falsy '.++$falsy => [false, ''],

            'falsy '.++$falsy => [0, null],
            'falsy '.++$falsy => [0, false],
            'falsy '.++$falsy => [0, 0.0],
            'falsy '.++$falsy => [0, ''],
            'falsy '.++$falsy => [0.0, null],
            'falsy '.++$falsy => [0.0, false],
            'falsy '.++$falsy => [0.0, 0],
            'falsy '.++$falsy => [0.0, ''],
            'truthy '.++$truthy => [true, 1],
            'truthy '.++$truthy => [true, 1.0],
            'truthy '.++$truthy => [true, '1'],
            'truthy '.++$truthy => [1, true],
            'truthy '.++$truthy => [1, 1.0],
            'truthy '.++$truthy => [1, '1'],
            'truthy '.++$truthy => [1.0, true],
            'truthy '.++$truthy => [1.0, 1],
            'truthy '.++$truthy => [1.0, '1'],
        ];
    }

    final public static function provideSameData(): array
    {
        $dateTimeImmutable = new \DateTimeImmutable(
            '2025-02-16T00:25:30+01:00',
            new \DateTimeZone('Europe/Zurich')
        );

        $arrayIterator = new \ArrayIterator(['null' => null, 'boolean' => true, 'int' => 42, 'float' => 3.14, 'string' => 'string']);

        $sample = new Sample('name', 'value');

        return [
            'null' => [null, null],
            'boolean' => [true, true],
            'int' => [42, 42],
            'float' => [3.14, 3.14],
            'string' => ['string', 'string'],
            'array' => [
                [
                    'null' => null,
                    'boolean' => true,
                    'int' => 5,
                    'float' => 9.81,
                    'string' => 'string',
                    \DateTimeImmutable::class => $dateTimeImmutable,
                ],
                [
                    'null' => null,
                    'boolean' => true,
                    'int' => 5,
                    'float' => 9.81,
                    'string' => 'string',
                    \DateTimeImmutable::class => $dateTimeImmutable,
                ],
            ],
            \DateTimeImmutable::class => [
                $dateTimeImmutable,
                $dateTimeImmutable,
            ],
            \ArrayIterator::class => [
                $arrayIterator,
                $arrayIterator,
            ],
            Sample::class => [
                $sample,
                $sample,
            ],
        ];
    }

    final public static function provideNotSameData(): array
    {
        return [
            'array' => [
                [
                    \DateTimeImmutable::class => new \DateTimeImmutable(
                        '2025-02-16T00:25:30+01:00',
                        new \DateTimeZone('Europe/Zurich')
                    ),
                ],
                [
                    \DateTimeImmutable::class => new \DateTimeImmutable(
                        '2025-02-16T00:25:30+01:00',
                        new \DateTimeZone('Europe/Zurich')
                    ),
                ],
            ],
            \DateTimeImmutable::class => [
                new \DateTimeImmutable(
                    '2025-02-16T00:25:30+01:00',
                    new \DateTimeZone('Europe/Zurich')
                ),
                new \DateTimeImmutable(
                    '2025-02-16T00:25:30+01:00',
                    new \DateTimeZone('Europe/Zurich')
                ),
            ],
            \ArrayIterator::class => [
                new \ArrayIterator(['null' => null, 'boolean' => true, 'int' => 42, 'float' => 3.14, 'string' => 'string']),
                new \ArrayIterator(['null' => null, 'boolean' => true, 'int' => 42, 'float' => 3.14, 'string' => 'string']),
            ],
            Sample::class => [
                new Sample('name', 'value'),
                new Sample('name', 'value'),
            ],
        ];
    }

    final public static function provideEqualData(): array
    {
        return [
            'null' => [null, null],
            'boolean' => [true, true],
            'int' => [42, 42],
            'float' => [3.14, 3.14],
            'string' => ['string', 'string'],
            'array' => [
                [
                    'null' => null,
                    'boolean' => true,
                    'int' => 5,
                    'float' => 9.81,
                    'string' => 'string',
                    \DateTimeImmutable::class => new \DateTimeImmutable(
                        '2025-02-16T00:25:30+01:00',
                        new \DateTimeZone('Europe/Zurich')
                    ),
                ],
                [
                    'null' => null,
                    'boolean' => true,
                    'int' => 5,
                    'float' => 9.81,
                    'string' => 'string',
                    \DateTimeImmutable::class => new \DateTimeImmutable(
                        '2025-02-16T00:25:30+01:00',
                        new \DateTimeZone('Europe/Zurich')
                    ),
                ],
            ],
            \DateTimeImmutable::class => [
                new \DateTimeImmutable(
                    '2025-02-16T00:25:30+01:00',
                    new \DateTimeZone('Europe/Zurich')
                ),
                new \DateTimeImmutable(
                    '2025-02-16T00:25:30+01:00',
                    new \DateTimeZone('Europe/Zurich')
                ),
            ],
            \ArrayIterator::class => [
                new \ArrayIterator(['null' => null, 'boolean' => true, 'int' => 42, 'float' => 3.14, 'string' => 'string']),
                new \ArrayIterator(['null' => null, 'boolean' => true, 'int' => 42, 'float' => 3.14, 'string' => 'string']),
            ],
            Sample::class => [
                new Sample('name', 'value'),
                new Sample('name', 'value'),
            ],
        ];
    }

    final public static function provideNotEqualData(): array
    {
        return [
            'array1' => [
                [
                    \DateTimeImmutable::class => new \DateTimeImmutable(
                        '2025-02-16T00:25:30+01:00',
                        new \DateTimeZone('Europe/Zurich')
                    ),
                ],
                [
                    \DateTimeImmutable::class => new \DateTimeImmutable(
                        '2025-02-17T00:25:30+01:00',
                        new \DateTimeZone('Europe/Zurich')
                    ),
                ],
            ],
            'array2' => [
                [
                    'key1' => 'value',
                ],
                [
                    'key2' => 'value',
                ],
            ],
            'array3' => [
                [
                    'key' => 'value',
                ],
                [],
            ],
            \DateTimeImmutable::class => [
                new \DateTimeImmutable(
                    '2025-02-16T00:25:30+01:00',
                    new \DateTimeZone('Europe/Zurich')
                ),
                new \DateTimeImmutable(
                    '2025-02-17T00:25:30+01:00',
                    new \DateTimeZone('Europe/Zurich')
                ),
            ],
            \ArrayIterator::class => [
                new \ArrayIterator(['null' => null, 'boolean' => true, 'int' => 42, 'float' => 3.14, 'string' => 'string1']),
                new \ArrayIterator(['null' => null, 'boolean' => true, 'int' => 42, 'float' => 3.14, 'string' => 'string2']),
            ],
            Sample::class => [
                new Sample('name', 'value1'),
                new Sample('name', 'value2'),
            ],
        ];
    }
}
