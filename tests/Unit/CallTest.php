<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Unit;

use Chubbyphp\Mock\Call;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Mock\Call
 *
 * @internal
 */
class CallTest extends TestCase
{
    public function testEmptyWith()
    {
        $call = Call::create('method')->with();

        self::assertSame('method', $call->getMethod());
        self::assertTrue($call->hasWith());
        self::assertFalse($call->hasReturnSelf());
        self::assertFalse($call->hasReturn());
        self::assertFalse($call->hasReturnCallback());
        self::assertSame([], $call->getWith());
        self::assertNull($call->getException());
        self::assertNull($call->getReturn());
        self::assertNull($call->getReturnCallback());
    }

    public function testWith()
    {
        $call = Call::create('method')->with('argument');

        self::assertSame('method', $call->getMethod());
        self::assertTrue($call->hasWith());
        self::assertFalse($call->hasReturnSelf());
        self::assertFalse($call->hasReturn());
        self::assertFalse($call->hasReturnCallback());
        self::assertSame(['argument'], $call->getWith());
        self::assertNull($call->getException());
        self::assertNull($call->getReturn());
        self::assertNull($call->getReturnCallback());
    }

    public function testError()
    {
        $exception = new \Error();

        $call = Call::create('method')->willThrowException($exception);

        self::assertSame('method', $call->getMethod());
        self::assertFalse($call->hasWith());
        self::assertFalse($call->hasReturnSelf());
        self::assertFalse($call->hasReturn());
        self::assertFalse($call->hasReturnCallback());
        self::assertSame([], $call->getWith());
        self::assertSame($exception, $call->getException());
        self::assertNull($call->getReturn());
        self::assertNull($call->getReturnCallback());
    }

    public function testException()
    {
        $exception = new \Exception();

        $call = Call::create('method')->willThrowException($exception);

        self::assertSame('method', $call->getMethod());
        self::assertFalse($call->hasWith());
        self::assertFalse($call->hasReturnSelf());
        self::assertFalse($call->hasReturn());
        self::assertFalse($call->hasReturnCallback());
        self::assertSame([], $call->getWith());
        self::assertSame($exception, $call->getException());
        self::assertNull($call->getReturn());
        self::assertNull($call->getReturnCallback());
    }

    public function testReturnSelf()
    {
        $call = Call::create('method')->willReturnSelf();

        self::assertSame('method', $call->getMethod());
        self::assertFalse($call->hasWith());
        self::assertTrue($call->hasReturnSelf());
        self::assertFalse($call->hasReturn());
        self::assertFalse($call->hasReturnCallback());
        self::assertSame([], $call->getWith());
        self::assertNull($call->getException());
        self::assertNull($call->getReturn());
        self::assertNull($call->getReturnCallback());
    }

    public function testReturn()
    {
        $return = 'test';

        $call = Call::create('method')->willReturn($return);

        self::assertSame('method', $call->getMethod());
        self::assertFalse($call->hasWith());
        self::assertFalse($call->hasReturnSelf());
        self::assertTrue($call->hasReturn());
        self::assertFalse($call->hasReturnCallback());
        self::assertSame([], $call->getWith());
        self::assertNull($call->getException());
        self::assertSame($return, $call->getReturn());
        self::assertNull($call->getReturnCallback());
    }

    public function testReturnCallback()
    {
        $returnCallback = function () {};

        $call = Call::create('method')->willReturnCallback($returnCallback);

        self::assertSame('method', $call->getMethod());
        self::assertFalse($call->hasWith());
        self::assertFalse($call->hasReturnSelf());
        self::assertFalse($call->hasReturn());
        self::assertTrue($call->hasReturnCallback());
        self::assertSame([], $call->getWith());
        self::assertNull($call->getException());
        self::assertNull($call->getReturn());
        self::assertSame($returnCallback, $call->getReturnCallback());
    }

    public function testTryExceptionAndReturnSelf()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willReturnSelf: There is already a exception');

        $call = Call::create('method')
            ->willThrowException(new \Exception())
            ->willReturnSelf()
        ;
    }

    public function testTryExceptionAndReturn()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willReturn: There is already a exception');

        $call = Call::create('method')
            ->willThrowException(new \Exception())
            ->willReturn('test')
        ;
    }

    public function testTryExceptionAndReturnCallback()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willReturnCallback: There is already a exception');

        $call = Call::create('method')
            ->willThrowException(new \Exception())
            ->willReturnCallback(function () {})
        ;
    }

    public function testTryReturnSelfAndException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willThrowException: There is already a return self');

        $call = Call::create('method')
            ->willReturnSelf()
            ->willThrowException(new \Exception())
        ;
    }

    public function testTryReturnSelfAndReturn()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willReturn: There is already a return self');

        $call = Call::create('method')
            ->willReturnSelf()
            ->willReturn('test')
        ;
    }

    public function testTryReturnSelfAndReturnCallback()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willReturnCallback: There is already a return self');

        $call = Call::create('method')
            ->willReturnSelf()
            ->willReturnCallback(function () {})
        ;
    }

    public function testTryReturnAndException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willThrowException: There is already a return');

        $call = Call::create('method')
            ->willReturn('test')
            ->willThrowException(new \Exception())
        ;
    }

    public function testTryReturnAndReturnSelf()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willReturnSelf: There is already a return');

        $call = Call::create('method')
            ->willReturn('test')
            ->willReturnSelf()
        ;
    }

    public function testTryReturnAndReturnCallback()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willReturnCallback: There is already a return');

        $call = Call::create('method')
            ->willReturn('test')
            ->willReturnCallback(function () {})
        ;
    }

    public function testTryReturnCallbackAndException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willThrowException: There is already a return callback');

        $call = Call::create('method')
            ->willReturnCallback(function () {})
            ->willThrowException(new \Exception())
        ;
    }

    public function testTryReturnCallbackAndReturnSelf()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willReturnSelf: There is already a return callback');

        $call = Call::create('method')
            ->willReturnCallback(function () {})
            ->willReturnSelf()
        ;
    }

    public function testTryReturnCallbackAndReturn()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willReturn: There is already a return callback');

        $call = Call::create('method')
            ->willReturnCallback(function () {})
            ->willReturn('test')
        ;
    }
}
