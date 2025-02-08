<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Unit\Exceptions;

use Chubbyphp\Mock\Exceptions\AbstractMock;
use Chubbyphp\Tests\Mock\Sample\Sample;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

enum Suit
{
    case Hearts;
    case Diamonds;
    case Clubs;
    case Spades;
}

enum BackedSuit: string
{
    case Hearts = 'H';
    case Diamonds = 'D';
    case Clubs = 'C';
    case Spades = 'S';
}

enum BackedEmpty: string {}

/**
 * @covers \Chubbyphp\Mock\Exceptions\AbstractMock
 *
 * @internal
 */
final class AbstractMockTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testGetData(mixed $expected, mixed $actual): void
    {
        $mockException = new class extends AbstractMock {
            public function publicGetData(mixed $value): mixed
            {
                return $this->getData($value);
            }
        };

        self::assertSame($expected, $mockException->publicGetData($actual));
    }

    public static function provideData(): array
    {
        $array = ['null' => null, 'boolean' => true, 'int' => 5, 'float' => 3.14, 'string' => 'string'];

        $object = (object) $array;

        $objectWithRecursion = clone $object;
        $objectWithRecursion->objectWithRecursion = $objectWithRecursion;

        $exception = new \Exception('message', 42, new \Exception('previous', 5));

        return [
            'null' => [null, null],
            'true' => [true, true],
            'false' => [false, false],
            'int' => [5, 5],
            'float' => [3.14, 3.14],
            'string' => ['string', 'string'],
            'array' => [$array, $array],
            \ArrayIterator::class => [
                [0, $array, [], null, '__CLASS__' => \ArrayIterator::class],
                new \ArrayIterator($array),
            ],
            \DateTimeImmutable::class => [
                [
                    'date' => '2025-02-16 00:25:30.000000',
                    'timezone_type' => 1,
                    'timezone' => '+01:00',
                    '__CLASS__' => 'DateTimeImmutable',
                ],
                new \DateTimeImmutable('2025-02-16T00:25:30+01:00', new \DateTimeZone('Europe/Zurich')),
            ],
            Sample::class => [
                ['name' => 'name', 'value' => 'value', '__CLASS__' => Sample::class],
                new Sample('name', 'value'),
            ],
            \stdClass::class => [[...$array, '__CLASS__' => \stdClass::class], $object],
            \stdClass::class.' (recursion)' => [
                [...$array, 'objectWithRecursion' => '(recursion)', '__CLASS__' => \stdClass::class],
                $objectWithRecursion,
            ],
            \Exception::class => [
                [
                    'message' => 'message',
                    'code' => 42,
                    'file' => '(project)/tests/Unit/Exceptions/AbstractMockTest.php',
                    'line' => 59,
                    'previous' => [
                        'message' => 'previous',
                        'code' => 5,
                        'file' => '(project)/tests/Unit/Exceptions/AbstractMockTest.php',
                        'line' => 59,
                        'previous' => null,
                        '__CLASS__' => 'Exception',
                    ],
                    '__CLASS__' => 'Exception',
                ],
                $exception,
            ],
            \Closure::class => [['__CLASS__' => 'Closure'], static function (): void {}],
            Suit::class => [
                [
                    'name' => 'Diamonds',
                    '__CLASS__' => 'Chubbyphp\Tests\Mock\Unit\Exceptions\Suit',
                ],
                Suit::Diamonds,
            ],
            BackedSuit::class => [
                [
                    'name' => 'Diamonds',
                    'value' => 'D',
                    '__CLASS__' => 'Chubbyphp\Tests\Mock\Unit\Exceptions\BackedSuit',
                ],
                BackedSuit::Diamonds,
            ],
            'resource (stream)' => ['(resource)', fopen('php://memory', 'w')],
        ];
    }
}
