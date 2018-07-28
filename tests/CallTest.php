<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock;

use Chubbyphp\Mock\Call;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Mock\Call
 */
class CallTest extends TestCase
{
    public function testEmptyWith()
    {
        $call = Call::create('method')->with();

        self::assertSame('method', $call->getMethod());
        self::assertTrue($call->hasWith());
        self::assertFalse($call->hasReturn());
        self::assertFalse($call->hasReturnSelf());
        self::assertSame([], $call->getWith());
        self::assertNull($call->getException());
        self::assertNull($call->getReturn());
    }

    public function testWith()
    {
        $call = Call::create('method')->with('argument');

        self::assertSame('method', $call->getMethod());
        self::assertTrue($call->hasWith());
        self::assertFalse($call->hasReturn());
        self::assertFalse($call->hasReturnSelf());
        self::assertSame(['argument'], $call->getWith());
        self::assertNull($call->getException());
        self::assertNull($call->getReturn());
    }

    public function testError()
    {
        $exception = new \Error();

        $call = Call::create('method')->willThrowException($exception);

        self::assertSame('method', $call->getMethod());
        self::assertFalse($call->hasWith());
        self::assertFalse($call->hasReturn());
        self::assertFalse($call->hasReturnSelf());
        self::assertSame([], $call->getWith());
        self::assertSame($exception, $call->getException());
        self::assertNull($call->getReturn());
    }

    public function testException()
    {
        $exception = new \Exception();

        $call = Call::create('method')->willThrowException($exception);

        self::assertSame('method', $call->getMethod());
        self::assertFalse($call->hasWith());
        self::assertFalse($call->hasReturn());
        self::assertFalse($call->hasReturnSelf());
        self::assertSame([], $call->getWith());
        self::assertSame($exception, $call->getException());
        self::assertNull($call->getReturn());
    }

    public function testReturn()
    {
        $return = 'test';

        $call = Call::create('method')->willReturn($return);

        self::assertSame('method', $call->getMethod());
        self::assertFalse($call->hasWith());
        self::assertTrue($call->hasReturn());
        self::assertFalse($call->hasReturnSelf());
        self::assertSame([], $call->getWith());
        self::assertNull($call->getException());
        self::assertSame($return, $call->getReturn());
    }

    public function testReturnSelf()
    {
        $call = Call::create('method')->willReturnSelf();

        self::assertSame('method', $call->getMethod());
        self::assertFalse($call->hasWith());
        self::assertFalse($call->hasReturn());
        self::assertTrue($call->hasReturnSelf());
        self::assertSame([], $call->getWith());
        self::assertNull($call->getException());
        self::assertNull($call->getReturn());
    }

    public function testTryExceptionAndReturn()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willReturn: There is already a exception');

        $call = Call::create('method')
            ->willThrowException(new \Exception())
            ->willReturn('test');
    }

    public function testTryExceptionAndReturnSelf()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willReturnSelf: There is already a exception');

        $call = Call::create('method')
            ->willThrowException(new \Exception())
            ->willReturnSelf();
    }

    public function testTryReturnAndException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willThrowException: There is already a return');

        $call = Call::create('method')
            ->willReturn('test')
            ->willThrowException(new \Exception());
    }

    public function testTryReturnAndReturnSelf()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willReturnSelf: There is already a return');

        $call = Call::create('method')
            ->willReturn('test')
            ->willReturnSelf(new \Exception());
    }

    public function testTryReturnSelfAndException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willThrowException: There is already a return self');

        $call = Call::create('method')
            ->willReturnSelf()
            ->willThrowException(new \Exception());
    }

    public function testTryReturnSelfAndReturn()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willReturn: There is already a return self');

        $call = Call::create('method')
            ->willReturnSelf()
            ->willReturn('test');
    }
}
