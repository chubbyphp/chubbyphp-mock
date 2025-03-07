<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Unit;

use Chubbyphp\Mock\Exceptions\ParameterMismatch;
use Chubbyphp\Mock\MockMethod\WithCallback;
use Chubbyphp\Mock\MockMethod\WithoutReturn;
use Chubbyphp\Mock\MockMethod\WithReturn;
use Chubbyphp\Mock\MockMethod\WithReturnSelf;
use Chubbyphp\Mock\MockObjectBuilder;
use Chubbyphp\Tests\Mock\Sample\AbstractMethods;
use Chubbyphp\Tests\Mock\Sample\ByReference;
use Chubbyphp\Tests\Mock\Sample\DefaultParameters;
use Chubbyphp\Tests\Mock\Sample\NestedWithParents;
use Chubbyphp\Tests\Mock\Sample\ParentA;
use Chubbyphp\Tests\Mock\Sample\ParentB;
use Chubbyphp\Tests\Mock\Sample\ParentC;
use Chubbyphp\Tests\Mock\Sample\ParentD;
use Chubbyphp\Tests\Mock\Sample\PingRequestHandler;
use Chubbyphp\Tests\Mock\Sample\Sample;
use Chubbyphp\Tests\Mock\Sample\Variadic;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @covers \Chubbyphp\Mock\MockClassBuilder
 * @covers \Chubbyphp\Mock\MockObjectBuilder
 *
 * @internal
 */
final class MockObjectBuilderTest extends TestCase
{
    public function testWithAbstractMethods(): void
    {
        $builder = new MockObjectBuilder();

        $input = 'THIS IS A TEST';
        $output = strtolower($input);

        $abstractMethods = $builder->create(AbstractMethods::class, [
            new WithReturn('toLower', [$input], $output),
        ]);

        self::assertSame($output, $abstractMethods->toLower($input));
    }

    public function testWithByReference(): void
    {
        $builder = new MockObjectBuilder();

        $text = 'THIS IS A TEST';

        $byReference = $builder->create(ByReference::class, [
            new WithReturnSelf('toLower', [$text]),
        ]);

        self::assertSame($byReference, $byReference->toLower($text));
    }

    #[DoesNotPerformAssertions]
    public function testWithDefaultParameters(): void
    {
        $builder = new MockObjectBuilder();

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
                ['null' => null, 'boolean' => true, 'int' => 5, 'float' => 9.81, 'string' => 'string'],
                ['null' => null, 'boolean' => true, 'int' => 5, 'float' => 9.81, 'string' => 'string'],
                new \DateTimeImmutable('2025-02-16T00:25:30+01:00', new \DateTimeZone('Europe/Zurich')),
                new \ArrayIterator(['null' => null, 'boolean' => true, 'int' => 5, 'float' => 9.81, 'string' => 'string']),
                new Sample('name', 'value'),
            ], false),
        ]);

        $defaultParameters->defaultParameters();
    }

    public function testNestedWithParents(): void
    {
        $builder = new MockObjectBuilder();

        $nestedWithParents = $builder->create(NestedWithParents::class, [
            new WithCallback('d', static function ($self) {
                self::assertSame(ParentD::class, $self::class);

                return $self;
            }),
            new WithCallback('dc', static function ($parent) {
                self::assertSame(ParentC::class, $parent::class);

                return $parent;
            }),
            new WithCallback('c', static function ($self) {
                self::assertSame(ParentC::class, $self::class);

                return $self;
            }),
            new WithCallback('cb', static function ($parent) {
                self::assertSame(ParentB::class, $parent::class);

                return $parent;
            }),
            new WithCallback('b', static function ($self) {
                self::assertSame(ParentB::class, $self::class);

                return $self;
            }),
            new WithCallback('ba', static function ($parent) {
                self::assertSame(ParentA::class, $parent::class);

                return $parent;
            }),
            new WithCallback('a', static function ($self) {
                self::assertSame(ParentA::class, $self::class);

                return $self;
            }),
        ]);

        $nestedWithParents->d();
        $nestedWithParents->dc();
        $nestedWithParents->c();
        $nestedWithParents->cb();
        $nestedWithParents->b();
        $nestedWithParents->ba();
        $nestedWithParents->a();
    }

    public function testWithPingRequestHandler(): void
    {
        $builder = new MockObjectBuilder();

        $request = $builder->create(ServerRequestInterface::class, []);

        $responseBody = $builder->create(StreamInterface::class, [
            new WithCallback('write', static function (string $string): int {
                $data = json_decode($string, true);
                self::assertArrayHasKey('date', $data);

                return \strlen($string);
            }),
        ]);

        $response = $builder->create(ResponseInterface::class, [
            new WithReturnSelf('withHeader', ['Content-Type', 'application/json']),
            new WithReturnSelf('withHeader', ['Cache-Control', 'no-cache, no-store, must-revalidate']),
            new WithReturnSelf('withHeader', ['Pragma', 'no-cache']),
            new WithReturnSelf('withHeader', ['Expires', '0']),
            new WithReturn('getBody', [], $responseBody),
        ]);

        $responseFactory = $builder->create(ResponseFactoryInterface::class, [
            new WithReturn('createResponse', [200, ''], $response),
        ]);

        $requestHandler = new PingRequestHandler($responseFactory);

        self::assertSame($response, $requestHandler->handle($request));
    }

    public function testWithSample(): void
    {
        $builder = new MockObjectBuilder();

        $sample = $builder->create(Sample::class, [
            new WithReturn('getPrevious', [], null),
        ]);

        self::assertNull($sample->getPrevious());
    }

    public function testWithVariadic(): void
    {
        $builder = new MockObjectBuilder();

        $variadic = $builder->create(Variadic::class, [
            new WithReturn('join', ['|', ['string1', 'string2']], 'string1|string2'),
        ]);

        self::assertSame('string1|string2', $variadic->join('|', 'string1', 'string2'));
    }

    public function testWithDateTimeImmutable(): void
    {
        $builder = new MockObjectBuilder();

        $dateTimeImmutable = $builder->create(\DateTimeImmutable::class, [
            new WithReturn('format', ['c'], '2025-02-16T00:25:30+01:00'),
        ]);

        self::assertSame('2025-02-16T00:25:30+01:00', $dateTimeImmutable->format('c'));

        $dateTimeImmutable = $builder->create(\DateTimeImmutable::class, [
            new WithReturn('format', ['c'], '2025-02-20T22:22:22+01:00'),
        ]);

        self::assertSame('2025-02-20T22:22:22+01:00', $dateTimeImmutable->format('c'));
    }

    public function testWithDateTimeImmutableWithNotMatchingMock(): void
    {
        $builder = new MockObjectBuilder();

        $line = __LINE__ + 1;
        $dateTimeImmutable = $builder->create(\DateTimeImmutable::class, [
            new WithReturn('format', ['c'], '2025-02-16T00:25:30+01:00'),
        ]);

        try {
            $dateTimeImmutable->format('d-m-Y');

            throw new \Exception('should not be reachable');
        } catch (ParameterMismatch $e) {
            self::assertSame(<<<EOT
                {
                    "in": "(project)\\/tests\\/Unit\\/MockObjectBuilderTest.php:{$line}",
                    "class": "DateTimeImmutable",
                    "index": 0,
                    "methodName": "format",
                    "parameterIndex": 0,
                    "actualParameter": "d-m-Y",
                    "expectedParameter": "c",
                    "strict": true
                }
                EOT, $e->getMessage());
        }
    }
}
