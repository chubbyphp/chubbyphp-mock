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

A helper trait simplify mocking within phpunit.

## Requirements

 * php: ^8.2

## Installation

Through [Composer](http://getcomposer.org) as [chubbyphp/chubbyphp-mock][1].

```sh
composer require chubbyphp/chubbyphp-mock "^2.0" --dev
```

## Usage

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
}
```

## Upgrade from 1.x

**IMPORTANT**: If there is any use of `Chubbyphp\Mock\Argument\ArgumentCallback`, `Chubbyphp\Mock\Argument\ArgumentInstanceOf` or any other custom implementation of `Chubbyphp\Mock\Argument\ArgumentInterface` go to `Call with any implementation of Chubbyphp\Mock\Argument\ArgumentInterface`.

### Call with ->willReturn

old

```php
<?php

use Chubbyphp\Mock\Call;

Call::create('methodName')->with('parameter1')->willReturn('returnValue');
Call::create('methodName')->willReturn('returnValue');
```

new

```php
<?php

use Chubbyphp\Mock\MockMethod\WithReturn;

new WithReturn('methodName', ['parameter1'], 'returnValue');
new WithReturn('methodName', [], 'returnValue');
```

### Call with ->willReturnSelf

#### old

```php
<?php

use Chubbyphp\Mock\Call;

Call::create('methodName')->with('parameter1')->willReturnSelf();
Call::create('methodName')->willReturnSelf();
```

#### new

```php
<?php

use Chubbyphp\Mock\MockMethod\WithReturnSelf;

new WithReturnSelf('methodName', ['parameter1']);
new WithReturnSelf('methodName', []);
```

### Call with ->willThrowException

#### old

```php
<?php

use Chubbyphp\Mock\Call;

Call::create('methodName')->with('parameter1')->willThrowException($exception);
Call::create('methodName')-->willThrowException($exception);
```

#### new

```php
<?php

use Chubbyphp\Mock\MockMethod\WithException;

new WithException('methodName', ['parameter1']);
new WithException('methodName', []);
```

### Call without any ->will...

#### old

```php
<?php

use Chubbyphp\Mock\Call;

Call::create('methodName')->with('parameter1');
Call::create('methodName');
```

#### new

```php
<?php

use Chubbyphp\Mock\MockMethod\WithReturn;

new WithoutReturn('methodName', ['parameter1']);
new WithoutReturn('methodName', []);
```

### Call with any implementation of Chubbyphp\Mock\Argument\ArgumentInterface

#### old

```php
<?php

use Chubbyphp\Mock\Argument\ArgumentInstanceOf;
use Chubbyphp\Mock\Call;

Call::create('format')->with(new ArgumentInstanceOf(\DateTime::class), 'c')->willReturn('2004-02-12T15:19:21+00:00');
```
#### new

```php
<?php

use Chubbyphp\Mock\MockMethod\WithCallback;

new WithCallback('format', static function ($date, $format) {
    self::assertInstanceOf(\DateTime::class, $date);
    self::assertSame('c', $format);

    return '2004-02-12T15:19:21+00:00';
});
```

## Copyright

2025 Dominik Zogg


[1]: https://packagist.org/packages/chubbyphp/chubbyphp-mock
