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
final class CallTest extends TestCase
{
    public function testEmptyWith(): void
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

    public function testWith(): void
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

    public function testError(): void
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

    public function testException(): void
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

    public function testReturnSelf(): void
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

    public function testReturn(): void
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

    public function testReturnCallback(): void
    {
        $returnCallback = static function (): void {};

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

    public function testTryExceptionAndReturnSelf(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willReturnSelf: There is already a exception');

        $call = Call::create('method')
            ->willThrowException(new \Exception())
            ->willReturnSelf()
        ;
    }

    public function testTryExceptionAndReturn(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willReturn: There is already a exception');

        $call = Call::create('method')
            ->willThrowException(new \Exception())
            ->willReturn('test')
        ;
    }

    public function testTryExceptionAndReturnCallback(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willReturnCallback: There is already a exception');

        $call = Call::create('method')
            ->willThrowException(new \Exception())
            ->willReturnCallback(static function (): void {})
        ;
    }

    public function testTryReturnSelfAndException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willThrowException: There is already a return self');

        $call = Call::create('method')
            ->willReturnSelf()
            ->willThrowException(new \Exception())
        ;
    }

    public function testTryReturnSelfAndReturn(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willReturn: There is already a return self');

        $call = Call::create('method')
            ->willReturnSelf()
            ->willReturn('test')
        ;
    }

    public function testTryReturnSelfAndReturnCallback(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willReturnCallback: There is already a return self');

        $call = Call::create('method')
            ->willReturnSelf()
            ->willReturnCallback(static function (): void {})
        ;
    }

    public function testTryReturnAndException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willThrowException: There is already a return');

        $call = Call::create('method')
            ->willReturn('test')
            ->willThrowException(new \Exception())
        ;
    }

    public function testTryReturnAndReturnSelf(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willReturnSelf: There is already a return');

        $call = Call::create('method')
            ->willReturn('test')
            ->willReturnSelf()
        ;
    }

    public function testTryReturnAndReturnCallback(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willReturnCallback: There is already a return');

        $call = Call::create('method')
            ->willReturn('test')
            ->willReturnCallback(static function (): void {})
        ;
    }

    public function testTryReturnCallbackAndException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willThrowException: There is already a return callback');

        $call = Call::create('method')
            ->willReturnCallback(static function (): void {})
            ->willThrowException(new \Exception())
        ;
    }

    public function testTryReturnCallbackAndReturnSelf(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willReturnSelf: There is already a return callback');

        $call = Call::create('method')
            ->willReturnCallback(static function (): void {})
            ->willReturnSelf()
        ;
    }

    public function testTryReturnCallbackAndReturn(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Chubbyphp\Mock\Call::willReturn: There is already a return callback');

        $call = Call::create('method')
            ->willReturnCallback(static function (): void {})
            ->willReturn('test')
        ;
    }
}
