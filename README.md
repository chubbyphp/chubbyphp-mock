# chubbyphp-mock

[![Build Status](https://api.travis-ci.org/chubbyphp/chubbyphp-mock.png?branch=master)](https://travis-ci.org/chubbyphp/chubbyphp-mock)
[![Coverage Status](https://coveralls.io/repos/github/chubbyphp/chubbyphp-mock/badge.svg?branch=master)](https://coveralls.io/github/chubbyphp/chubbyphp-mock?branch=master)
[![Latest Stable Version](https://poser.pugx.org/chubbyphp/chubbyphp-mock/v/stable.png)](https://packagist.org/packages/chubbyphp/chubbyphp-mock)
[![Total Downloads](https://poser.pugx.org/chubbyphp/chubbyphp-mock/downloads.png)](https://packagist.org/packages/chubbyphp/chubbyphp-mock)
[![Monthly Downloads](https://poser.pugx.org/chubbyphp/chubbyphp-mock/d/monthly)](https://packagist.org/packages/chubbyphp/chubbyphp-mock)
[![Daily Downloads](https://poser.pugx.org/chubbyphp/chubbyphp-mock/d/daily)](https://packagist.org/packages/chubbyphp/chubbyphp-mock)

## Description

A helper trait simplify mocking within phpunit.

## Requirements

 * php: ^7.0
 * phpunit/phpunit: ^6.5|^7.0|^8.0|^9.0

## Installation

Through [Composer](http://getcomposer.org) as [chubbyphp/chubbyphp-mock][1].

```sh
composer require chubbyphp/chubbyphp-mock "^1.4" --dev
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
