# chubbyphp-mock

[![CI](https://github.com/chubbyphp/chubbyphp-mock/actions/workflows/ci.yml/badge.svg)](https://github.com/chubbyphp/chubbyphp-mock/actions/workflows/ci.yml)
[![Coverage Status](https://coveralls.io/repos/github/chubbyphp/chubbyphp-mock/badge.svg?branch=master)](https://coveralls.io/github/chubbyphp/chubbyphp-mock?branch=master)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fchubbyphp%2Fchubbyphp-mock%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/chubbyphp/chubbyphp-mock/master)
[![Latest Stable Version](https://poser.pugx.org/chubbyphp/chubbyphp-mock/v)](https://packagist.org/packages/chubbyphp/chubbyphp-mock)
[![Total Downloads](https://poser.pugx.org/chubbyphp/chubbyphp-mock/downloads)](https://packagist.org/packages/chubbyphp/chubbyphp-mock)
[![Monthly Downloads](https://poser.pugx.org/chubbyphp/chubbyphp-mock/d/monthly)](https://packagist.org/packages/chubbyphp/chubbyphp-mock)

[![bugs](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-mock&metric=bugs)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-mock)
[![code_smells](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-mock&metric=code_smells)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-mock)
[![coverage](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-mock&metric=coverage)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-mock)
[![duplicated_lines_density](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-mock&metric=duplicated_lines_density)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-mock)
[![ncloc](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-mock&metric=ncloc)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-mock)
[![sqale_rating](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-mock&metric=sqale_rating)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-mock)
[![alert_status](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-mock&metric=alert_status)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-mock)
[![reliability_rating](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-mock&metric=reliability_rating)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-mock)
[![security_rating](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-mock&metric=security_rating)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-mock)
[![sqale_index](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-mock&metric=sqale_index)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-mock)
[![vulnerabilities](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-mock&metric=vulnerabilities)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-mock)

## Description

A strict mocking solution. It works with any testing framework; the examples below use PHPUnit.

A mock is defined as an ordered list of expected method calls. Every call made on the mock must match the next expectation
in that list by method name and parameters, otherwise an exception is thrown. Missing calls are detected as well: a mock
that still has unconsumed expectations when it is destroyed throws too.

Every exception carries a JSON message with the mocked class, the position of the failing call, the actual and expected
values, and the file and line where the mock was created.

## Requirements

 * php: ^8.3
 * [nikic/php-parser][2]: ^5.8

## Installation

Through [Composer](http://getcomposer.org) as [chubbyphp/chubbyphp-mock][1].

```sh
composer require chubbyphp/chubbyphp-mock "^2.2" --dev
```

## Usage

Create a `MockObjectBuilder` and pass it the class or interface to mock together with the list of expected calls.
Each expected call is an instance of one of the following mock methods:

| Mock method      | Constructor                                                    | Behaviour                                                     |
|------------------|----------------------------------------------------------------|---------------------------------------------------------------|
| `WithoutReturn`  | `(string $name, array $parameters, bool $strict = true)`       | Validates the call and returns nothing.                       |
| `WithReturn`     | `(string $name, array $parameters, mixed $return, bool $strict = true)` | Validates the call and returns the given value.      |
| `WithReturnSelf` | `(string $name, array $parameters, bool $strict = true)`       | Validates the call and returns the mock itself (fluent APIs). |
| `WithException`  | `(string $name, array $parameters, \Throwable $exception, bool $strict = true)` | Validates the call and throws the given exception. |
| `WithCallback`   | `(string $name, callable $callback)`                           | Validates the method name and delegates to the callback with the actual parameters. Its return value is returned by the mock. |

Parameters are compared with `===` by default. Pass `$strict = false` to compare by value instead; arrays are then
compared entry by entry and objects property by property (or via `__serialize` / `__sleep` when available).

Use `WithCallback` whenever a parameter cannot be known in advance (for example a generated id or a timestamp) or when
you want to assert on it inside the callback.

```php
<?php

declare(strict_types=1);

namespace MyProject\Tests\Unit\RequestHandler;

use Chubbyphp\Mock\MockMethod\WithCallback;
use Chubbyphp\Mock\MockMethod\WithReturn;
use Chubbyphp\Mock\MockMethod\WithReturnSelf;
use Chubbyphp\Mock\MockObjectBuilder;
use MyProject\RequestHandler\PingRequestHandler;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

final class PingRequestHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $builder = new MockObjectBuilder();

        // no calls expected: any method call on this mock fails the test
        $request = $builder->create(ServerRequestInterface::class, []);

        $responseBody = $builder->create(StreamInterface::class, [
            // the written JSON is not known in advance, so assert on it in a callback
            new WithCallback('write', static function (string $string): int {
                $data = json_decode($string, true);
                self::assertArrayHasKey('datetime', $data);

                return \strlen($string);
            }),
        ]);

        $response = $builder->create(ResponseInterface::class, [
            // calls must happen in exactly this order
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
}
```

## Other testing frameworks

chubbyphp-mock has no dependency on PHPUnit. A host framework only needs to provide a place to build the mocks,
a way to assert inside a `WithCallback`, and a test scope that ends when the test ends.

Unconsumed expectations are reported from the mock's destructor. Keep mocks in local variables of the test and do not
store them in long-lived properties, statics or shared setup, otherwise the check is delayed and the failure is
attributed to the wrong place.

The examples below use the same `PingRequestHandler` scenario as the PHPUnit example above.

### Pest

`expect()` replaces the PHPUnit assertion inside the callback, everything else stays the same.

```php
<?php

declare(strict_types=1);

use Chubbyphp\Mock\MockMethod\WithCallback;
use Chubbyphp\Mock\MockMethod\WithReturn;
use Chubbyphp\Mock\MockMethod\WithReturnSelf;
use Chubbyphp\Mock\MockObjectBuilder;
use MyProject\RequestHandler\PingRequestHandler;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

it('handles a ping request', function (): void {
    $builder = new MockObjectBuilder();

    $request = $builder->create(ServerRequestInterface::class, []);

    $responseBody = $builder->create(StreamInterface::class, [
        new WithCallback('write', static function (string $string): int {
            expect(json_decode($string, true))->toHaveKey('datetime');

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

    expect($requestHandler->handle($request))->toBe($response);
});
```

### Codeception

Codeception unit tests are PHPUnit test cases with a different base class, so the PHPUnit example works unchanged.

```php
<?php

declare(strict_types=1);

namespace MyProject\Tests\Unit\RequestHandler;

use Chubbyphp\Mock\MockMethod\WithCallback;
use Chubbyphp\Mock\MockMethod\WithReturn;
use Chubbyphp\Mock\MockMethod\WithReturnSelf;
use Chubbyphp\Mock\MockObjectBuilder;
use Codeception\Test\Unit;
use MyProject\RequestHandler\PingRequestHandler;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

final class PingRequestHandlerTest extends Unit
{
    public function testHandle(): void
    {
        $builder = new MockObjectBuilder();

        $request = $builder->create(ServerRequestInterface::class, []);

        $responseBody = $builder->create(StreamInterface::class, [
            new WithCallback('write', function (string $string): int {
                $data = json_decode($string, true);
                $this->assertArrayHasKey('datetime', $data);

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

        $this->assertSame($response, $requestHandler->handle($request));
    }
}
```

### phpspec

phpspec injects lenient, unordered Prophecy collaborators by default. Building the mocks with chubbyphp-mock instead
gives the spec a strict, ordered script of expected calls.

```php
<?php

declare(strict_types=1);

namespace spec\MyProject\RequestHandler;

use Chubbyphp\Mock\MockMethod\WithCallback;
use Chubbyphp\Mock\MockMethod\WithReturn;
use Chubbyphp\Mock\MockMethod\WithReturnSelf;
use Chubbyphp\Mock\MockObjectBuilder;
use MyProject\RequestHandler\PingRequestHandler;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

final class PingRequestHandlerSpec extends ObjectBehavior
{
    public function it_handles_a_ping_request(): void
    {
        $builder = new MockObjectBuilder();

        $request = $builder->create(ServerRequestInterface::class, []);

        $responseBody = $builder->create(StreamInterface::class, [
            // phpspec has no standalone assertion API, so fail with an exception
            new WithCallback('write', static function (string $string): int {
                $data = json_decode($string, true);
                if (!\array_key_exists('datetime', $data)) {
                    throw new \RuntimeException('Missing key "datetime" in written JSON');
                }

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

        $this->beConstructedWith($responseFactory);

        $this->handle($request)->shouldReturn($response);
    }
}
```

## FAQ

### Howto mock final classes/methods

Use the third party package [dg/bypass-finals](https://packagist.org/packages/dg/bypass-finals).

**This does not remove the final keyword from internal (PHP core or extension) classes.**

### What Cannot Be Mocked

- **Static methods:**
  They are declared on the mock but throw when called.

- **Properties:**
  Only method calls are intercepted.

- **`__construct` and `__destruct`:**
  The mock defines its own constructor and destructor.

- **Internal final classes or methods:**
  Even with `dg/bypass-finals`, final internal classes or methods cannot be mocked.

- **Poorly built extension classes:**
  Some older PHP extensions declare classes that cannot be fully reverse-engineered via reflection. Those classes are not mockable.

### Special Handling

- **`\Traversable` and interfaces extending it:**
  PHP does not allow a userland class to implement `\Traversable` directly; it must implement `\Iterator` or `\IteratorAggregate` instead.
  The generated mock therefore additionally implements `\IteratorAggregate` and, if the mocked type does not declare it, a `getIterator()` method.
  That method behaves like any other mocked method and needs a matching expectation when called.

Please report if you find other restrictions / bugs.

## Upgrade

[Upgrade from 1.x](doc/upgrade-from-1.x.md)


## Copyright

2026 Dominik Zogg


[1]: https://packagist.org/packages/chubbyphp/chubbyphp-mock
[2]: https://packagist.org/packages/nikic/php-parser
