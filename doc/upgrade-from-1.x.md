
# Upgrade from 1.x

## Call with ->willReturn

### old

```php
<?php

declare(strict_types=1);

namespace MyProject\Tests;

use Chubbyphp\Mock\Argument\ArgumentCallback;
use Chubbyphp\Mock\Call;
use Chubbyphp\Mock\MockByCallsTrait;
use PHPUnit\Framework\TestCase;

final class MyTest extends TestCase
{
    use MockByCallsTrait;

    public function testMyMethod(): void
    {
        $myService = $this->getMockByCalls(MyService::class, [
            Call::create('methodName')->with('parameter1')->willReturn('returnValue'),
            Call::create('methodName')->willReturn('returnValue'),
            // or with any implementation of Chubbyphp\Mock\Argument\ArgumentInterface
            Call::create('methodName')
                ->with(new ArgumentCallback(static function ($parameter1) {
                    self::assertSame('parameter1', $parameter1);
                }))
                ->willReturn('returnValue')
        ]);
    }
}
```

### new

```php
<?php

declare(strict_types=1);

namespace MyProject\Tests;

use Chubbyphp\Mock\MockMethod\WithReturn;
use Chubbyphp\Mock\MockMethod\WithCallback;
use Chubbyphp\Mock\MockObjectBuilder;
use PHPUnit\Framework\TestCase;

final class MyTest extends TestCase
{
    public function testMyMethod(): void
    {
        $builder = new MockObjectBuilder();

        $myService = $builder->create(MyService::class, [
            new WithReturn('methodName', ['parameter1'], 'returnValue'),
            new WithReturn('methodName', [], 'returnValue'),
            new WithCallback('methodName', static function ($parameter1) {
                self::assertSame('parameter1', $parameter1);

                return 'returnValue';
            }),
        ]);
    }
}
```

## Call with ->willReturnSelf

### old

```php
<?php

declare(strict_types=1);

namespace MyProject\Tests;

use Chubbyphp\Mock\Argument\ArgumentCallback;
use Chubbyphp\Mock\Call;
use Chubbyphp\Mock\MockByCallsTrait;
use PHPUnit\Framework\TestCase;

final class MyTest extends TestCase
{
    use MockByCallsTrait;

    public function testMyMethod(): void
    {
        $myService = $this->getMockByCalls(MyService::class, [
            Call::create('methodName')->with('parameter1')->willReturnSelf(),
            Call::create('methodName')->willReturnSelf(),
            // or with any implementation of Chubbyphp\Mock\Argument\ArgumentInterface
            Call::create('methodName')
                ->with(new ArgumentCallback(static function ($parameter1) {
                    self::assertSame('parameter1', $parameter1);
                }))
                ->willReturnSelf(),
        ]);
    }
}
```

### new

```php
<?php

declare(strict_types=1);

namespace MyProject\Tests;

use Chubbyphp\Mock\MockMethod\WithReturnSelf;
use Chubbyphp\Mock\MockMethod\WithCallback;
use Chubbyphp\Mock\MockObjectBuilder;
use PHPUnit\Framework\TestCase;

final class MyTest extends TestCase
{
    public function testMyMethod(): void
    {
        $builder = new MockObjectBuilder();

        $myService = $builder->create(MyService::class, [
            new WithReturnSelf('methodName', ['parameter1']),
            new WithReturnSelf('methodName', []),
            new WithCallback('methodName', function ($parameter1) use (&$myService) {
                self::assertSame('parameter1', $parameter1);

                return $myService;
            }),
        ]);
    }
}
```

## Call with ->willThrowException

```php
<?php

declare(strict_types=1);

namespace MyProject\Tests;

use Chubbyphp\Mock\Argument\ArgumentCallback;
use Chubbyphp\Mock\Call;
use Chubbyphp\Mock\MockByCallsTrait;
use PHPUnit\Framework\TestCase;

final class MyTest extends TestCase
{
    use MockByCallsTrait;

    public function testMyMethod(): void
    {
        $exception = new \Exception();

        $myService = $this->getMockByCalls(MyService::class, [
            Call::create('methodName')->with('parameter1')->willThrowException($exception),
            Call::create('methodName')->willThrowException($exception),
            // or with any implementation of Chubbyphp\Mock\Argument\ArgumentInterface
            Call::create('methodName')
                ->with(new ArgumentCallback(static function ($parameter1) {
                    self::assertSame('parameter1', $parameter1);
                }))
                ->willThrowException($exception),
        ]);
    }
}
```

### new

```php
<?php

declare(strict_types=1);

namespace MyProject\Tests;

use Chubbyphp\Mock\MockMethod\WithException;
use Chubbyphp\Mock\MockMethod\WithCallback;
use Chubbyphp\Mock\MockObjectBuilder;
use PHPUnit\Framework\TestCase;

final class MyTest extends TestCase
{
    public function testMyMethod(): void
    {
        $builder = new MockObjectBuilder();

        $exception = new \Exception();

        $myService = $builder->create(MyService::class, [
            new WithException('methodName', ['parameter1'], $exception),
            new WithException('methodName', [], $exception),
            new WithCallback('methodName', static function ($parameter1) use ($exception) {
                self::assertSame('parameter1', $parameter1);

                throw $exception;
            }),
        ]);
    }
}
```

## Call without any ->will...

### old

```php
<?php

declare(strict_types=1);

namespace MyProject\Tests;

use Chubbyphp\Mock\Argument\ArgumentCallback;
use Chubbyphp\Mock\Call;
use Chubbyphp\Mock\MockByCallsTrait;
use PHPUnit\Framework\TestCase;

final class MyTest extends TestCase
{
    use MockByCallsTrait;

    public function testMyMethod(): void
    {
        $myService = $this->getMockByCalls(MyService::class, [
            Call::create('methodName')->with('parameter1'),
            Call::create('methodName'),
            // or with any implementation of Chubbyphp\Mock\Argument\ArgumentInterface
            Call::create('methodName')
                ->with(new ArgumentCallback(static function ($parameter1) {
                    self::assertSame('parameter1', $parameter1);
                }))
        ]);
    }
}
```

### new

```php
<?php

declare(strict_types=1);

namespace MyProject\Tests;

use Chubbyphp\Mock\MockMethod\WithoutReturn;
use Chubbyphp\Mock\MockMethod\WithCallback;
use Chubbyphp\Mock\MockObjectBuilder;
use PHPUnit\Framework\TestCase;

final class MyTest extends TestCase
{
    public function testMyMethod(): void
    {
        $builder = new MockObjectBuilder();

        $myService = $builder->create(MyService::class, [
            new WithoutReturn('methodName', ['parameter1']),
            new WithoutReturn('methodName', []),
            new WithCallback('methodName', static function ($parameter1) {
                self::assertSame('parameter1', $parameter1);
            }),
        ]);
    }
}
```
