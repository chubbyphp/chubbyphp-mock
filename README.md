# chubbyphp-mock

[![Build Status](https://api.travis-ci.org/chubbyphp/chubbyphp-mock.png?branch=master)](https://travis-ci.org/chubbyphp/chubbyphp-mock)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/chubbyphp/chubbyphp-mock/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/chubbyphp/chubbyphp-mock/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/chubbyphp/chubbyphp-mock/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/chubbyphp/chubbyphp-mock/?branch=master)
[![Total Downloads](https://poser.pugx.org/chubbyphp/chubbyphp-mock/downloads.png)](https://packagist.org/packages/chubbyphp/chubbyphp-mock)
[![Monthly Downloads](https://poser.pugx.org/chubbyphp/chubbyphp-mock/d/monthly)](https://packagist.org/packages/chubbyphp/chubbyphp-mock)
[![Latest Stable Version](https://poser.pugx.org/chubbyphp/chubbyphp-mock/v/stable.png)](https://packagist.org/packages/chubbyphp/chubbyphp-mock)
[![Latest Unstable Version](https://poser.pugx.org/chubbyphp/chubbyphp-mock/v/unstable)](https://packagist.org/packages/chubbyphp/chubbyphp-mock)

## Description

A simple mock solution for phpunit.

## Requirements

 * php: ~7.0
 * phpunit/phpunit: ^6.5|^7.0

## Installation

Through [Composer](http://getcomposer.org) as [chubbyphp/chubbyphp-mock][1].

```sh
composer require chubbyphp/chubbyphp-mock "~1.2"
```

## Usage

```php
<?php

declare(strict_types=1);

namespace MyProject\Tests;

use Chubbyphp\Mock\Argument\ArgumentInstanceOf;
use Chubbyphp\Mock\Call;
use Chubbyphp\Mock\MockByCallsTrait;
use MyProject\Services\DateTimeService;
use MyProject\Services\MainService;
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
        ]);

        $manager = new MainService($dateTimeService);
        $manager->execute();
    }
}
```

## Copyright

Dominik Zogg 2018


[1]: https://packagist.org/packages/chubbyphp/chubbyphp-mock
