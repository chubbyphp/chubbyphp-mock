<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Unit;

use Chubbyphp\Mock\Exceptions\ParameterMismatch;
use Chubbyphp\Mock\MockMethod\WithCallback;
use Chubbyphp\Mock\MockMethod\WithoutReturn;
use Chubbyphp\Mock\MockMethod\WithReturn;
use Chubbyphp\Mock\MockMethod\WithReturnSelf;
use Chubbyphp\Mock\MockObjectBuilder;
use Chubbyphp\Tests\Mock\Sample\DefaultParameters;
use Chubbyphp\Tests\Mock\Sample\PingRequestHandler;
use Chubbyphp\Tests\Mock\Sample\Sample;
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
    public function testWithSample(): void
    {
        $builder = new MockObjectBuilder();

        /** @var Sample $sample */
        $sample = $builder->create(Sample::class, [
            new WithReturn('getPrevious', [], null),
        ]);

        self::assertNull($sample->getPrevious());
    }

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
                ['null' => null, 'boolean' => true, 'int' => 5, 'float' => 9.81, 'string' => 'string'],
                new \DateTimeImmutable('2025-02-16T00:25:30+01:00', new \DateTimeZone('Europe/Zurich')),
                new \ArrayIterator(['null' => null, 'boolean' => true, 'int' => 5, 'float' => 9.81, 'string' => 'string']),
                new Sample('name', 'value'),
            ], false),
        ]);

        $defaultParameters->defaultParameters();
    }

    public function testWithPingRequestHandler(): void
    {
        $builder = new MockObjectBuilder();

        /** @var ServerRequestInterface $request */
        $request = $builder->create(ServerRequestInterface::class, []);

        /** @var StreamInterface $responseBody */
        $responseBody = $builder->create(StreamInterface::class, [
            new WithCallback('write', static function (string $string): int {
                $data = json_decode($string, true);
                self::assertArrayHasKey('date', $data);

                return \strlen($string);
            }),
        ]);

        /** @var ResponseInterface $response */
        $response = $builder->create(ResponseInterface::class, [
            new WithReturnSelf('withHeader', ['Content-Type', 'application/json']),
            new WithReturnSelf('withHeader', ['Cache-Control', 'no-cache, no-store, must-revalidate']),
            new WithReturnSelf('withHeader', ['Pragma', 'no-cache']),
            new WithReturnSelf('withHeader', ['Expires', '0']),
            new WithReturn('getBody', [], $responseBody),
        ]);

        /** @var ResponseFactoryInterface $responseFactory */
        $responseFactory = $builder->create(ResponseFactoryInterface::class, [
            new WithReturn('createResponse', [200, ''], $response),
        ]);

        $requestHandler = new PingRequestHandler($responseFactory);

        self::assertSame($response, $requestHandler->handle($request));
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

    public function testWithDateTimeImmutableWithNotMatchingMock(): void
    {
        $builder = new MockObjectBuilder();

        /** @var \DateTimeImmutable $dateTimeImmutable */
        $dateTimeImmutable = $builder->create(\DateTimeImmutable::class, [
            new WithReturn('format', ['c'], '2025-02-16T00:25:30+01:00'),
        ]);

        try {
            $dateTimeImmutable->format('d-m-Y');

            throw new \Exception('should not be reachable');
        } catch (ParameterMismatch $e) {
            self::assertSame(<<<'EOT'
                {
                    "in": "(project)\/tests\/Unit\/MockObjectBuilderTest.php:142",
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
