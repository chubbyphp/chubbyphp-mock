# chubbyphp-mock

[![Build Status](https://api.travis-ci.org/chubbyphp/chubbyphp-mock.png?branch=master)](https://travis-ci.org/chubbyphp/chubbyphp-mock)
[![Coverage Status](https://coveralls.io/repos/github/chubbyphp/chubbyphp-mock/badge.svg?branch=master)](https://coveralls.io/github/chubbyphp/chubbyphp-mock?branch=master)
[![Infection MSI](https://badge.stryker-mutator.io/github.com/chubbyphp/chubbyphp-mock/master)](https://travis-ci.org/chubbyphp/chubbyphp-mock)
[![Latest Stable Version](https://poser.pugx.org/chubbyphp/chubbyphp-mock/v/stable.png)](https://packagist.org/packages/chubbyphp/chubbyphp-mock)
[![Total Downloads](https://poser.pugx.org/chubbyphp/chubbyphp-mock/downloads.png)](https://packagist.org/packages/chubbyphp/chubbyphp-mock)
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

 * php: ^7.4|^8.0
 * phpunit/phpunit: ^9.3

## Installation

Through [Composer](http://getcomposer.org) as [chubbyphp/chubbyphp-mock][1].

```sh
composer require chubbyphp/chubbyphp-mock "^1.6" --dev
```

## Usage

```php
<?php

declare(strict_types=1);

namespace MyProject\Tests;

use Chubbyphp\Mock\Argument\ArgumentCallback;
use Chubbyphp\Mock\Argument\ArgumentInstanceOf;
use Chubbyphp\Mock\Call;
use Chubbyphp\Mock\MockByCallsTrait;
use MyProject\Services\DateTimeService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
    use MockByCallsTrait;

    public function testExecute()
    {
        /** @var DateTimeService|MockObject $dateTimeService */
        $dateTimeService = $this->getMockByCalls(DateTimeService::class, [
            Call::create('format')
                ->with(new ArgumentInstanceOf(\DateTime::class), 'c'),
                ->willReturn('2004-02-12T15:19:21+00:00')
            Call::create('format')
                ->with(
                    new ArgumentCallback(function ($dateTime) {
                        self::assertInstanceOf(\DateTime::class, $dateTime);
                    }),
                    'c'
                )
                ->willReturn('2008-05-23T08:12:55+00:00')
        ]);

        self::assertSame('2004-02-12T15:19:21+00:00' , $dateTimeService->format(new \DateTime(), 'c'));
        self::assertSame('2008-05-23T08:12:55+00:00' , $dateTimeService->format(new \DateTime(), 'c'));
    }
}
```

## FAQ

### Expectation failed for method name is anything when invoked <n...> time(s).

There is a mock with `$calls` given, but no method get called on the mock.

```php
/** @var User|MockObject $user */
$user = $this->getMockByCalls(User::class, [
    Call::create('getId')->with()->willReturn('a656cca7-7363-4ba7-875d-74bb0fd4f543'),
]);

// uncomment and test will be green
//$user->getId();
```

## Copyright

Dominik Zogg 2020


[1]: https://packagist.org/packages/chubbyphp/chubbyphp-mock
