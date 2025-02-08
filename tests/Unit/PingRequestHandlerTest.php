<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Unit;

use Chubbyphp\Mock\MockMethod\WithCallback;
use Chubbyphp\Mock\MockMethod\WithReturn;
use Chubbyphp\Mock\MockMethod\WithReturnSelf;
use Chubbyphp\Mock\MockObjectBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class PingRequestHandler implements RequestHandlerInterface
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(200)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->withHeader('Pragma', 'no-cache')
            ->withHeader('Expires', '0')
        ;

        /** @var non-empty-string $json */
        $json = json_encode(['date' => date('c')]);

        $response->getBody()->write($json);

        return $response;
    }
}

/**
 * @coversNothing
 *
 * @internal
 */
final class PingRequestHandlerTest extends TestCase
{
    public function testHandle(): void
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
}
