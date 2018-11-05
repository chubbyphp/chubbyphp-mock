<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock;

use Chubbyphp\Mock\Argument\ArgumentInstanceOf;
use Chubbyphp\Mock\Call;
use Chubbyphp\Mock\MockByCallsTrait;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Chubbyphp\Mock\MockByCallsTrait
 */
class MockByCallsTraitTest extends TestCase
{
    use MockByCallsTrait;

    public function testClassWithCallUsingArgumentInterface()
    {
        /** @var SampleClass|MockObject $mock */
        $mock = $this->getMockByCalls(SampleClass::class, [
            Call::create('sample')
                ->with(new ArgumentInstanceOf(\DateTime::class), true),
        ]);

        $mock->sample(new \DateTime());
    }

    public function testAbstractClassWithCallUsingArgumentInterface()
    {
        /** @var AbstractSampleClass|MockObject $mock */
        $mock = $this->getMockByCalls(AbstractSampleClass::class, [
            Call::create('sample')
                ->with(new ArgumentInstanceOf(\DateTime::class), true),
        ]);

        $mock->sample(new \DateTime());
    }

    public function testInterfaceWithInvalidCallUsingArgumentInterface()
    {
        /** @var SampleInterface|MockObject $mock */
        $mock = $this->getMockByCalls(SampleInterface::class, [
            Call::create('sample')
                ->with(new ArgumentInstanceOf(\stdClass::class), true),
        ]);

        try {
            $mock->sample(new \DateTime());
        } catch (ExpectationFailedException $e) {
            self::assertSame(
                'Method "sample" on class "Chubbyphp\Tests\Mock\SampleInterface" at call 0, argument 0'.PHP_EOL.
                'Failed asserting that DateTime Object (...) is an instance of class "stdClass".',
                $e->getMessage()
            );

            return;
        }

        self::fail(sprintf('Expected "%s"', ExpectationFailedException::class));
    }

    public function testInterfaceWithCallAndException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('sample');

        $argument1 = 'argument1';

        /** @var SampleInterface|MockObject $mock */
        $mock = $this->getMockByCalls(SampleInterface::class, [
            Call::create('sample')->with($argument1, true)->willThrowException(new \RuntimeException('sample')),
        ]);

        $mock->sample($argument1);
    }

    public function testInterfaceWithCallAndReturn()
    {
        $argument1 = 'argument1';
        $return = 'return';

        /** @var SampleInterface|MockObject $mock */
        $mock = $this->getMockByCalls(SampleInterface::class, [
            Call::create('sample')->with($argument1, true)->willReturn($return),
        ]);

        self::assertSame($return, $mock->sample($argument1));
    }

    public function testInterfaceWithCallAndReturnSelf()
    {
        $argument1 = 'argument1';

        /** @var SampleInterface|MockObject $mock */
        $mock = $this->getMockByCalls(SampleInterface::class, [
            Call::create('sample')->with($argument1, true)->willReturnSelf(),
        ]);

        self::assertSame($mock, $mock->sample($argument1));
    }

    public function testInterfacesWithCallAndReturnSelf()
    {
        $argument1 = 'argument1';
        $argument2 = 'argument2';

        /** @var SampleInterface|MockObject $mock */
        $mock = $this->getMockByCalls([SampleInterface::class, AdditionalSampleInterface::class], [
            Call::create('sample')->with($argument1, true)->willReturnSelf(),
            Call::create('additionalSample')->with($argument2, true)->willReturnSelf(),
        ]);

        self::assertSame($mock, $mock->sample($argument1));
        self::assertSame($mock, $mock->additionalSample($argument2));
    }

    public function testInterfaceWithoutCallsExpectsNoMethodGetCalled()
    {
        /** @var SampleInterface|MockObject $mock */
        $mock = $this->getMockByCalls(SampleInterface::class);

        clone $mock;

        try {
            $mock->sample('argument1');
        } catch (ExpectationFailedException $e) {
            self::assertSame(
                'Chubbyphp\Tests\Mock\SampleInterface::sample(\'argument1\', true) was not expected to be called.',
                $e->getMessage()
            );

            /** @var InvocationMocker $invocationMocker */
            $invocationMocker = $mock->__phpunit_getInvocationMocker();

            try {
                $invocationMocker->verify();
            } catch (ExpectationFailedException $e) {
                self::assertSame(
                    'Expectation failed for method name is anything when invoked 0 time(s).'.PHP_EOL.
                    'Method was expected to be called 0 times, actually called 1 times.'.PHP_EOL,
                    $e->getMessage()
                );

                $reflectionProperty = new \ReflectionProperty($mock, '__phpunit_invocationMocker');
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($mock, null);

                return;
            }

            self::fail('Expectation failed for method name is anything when invoked 0 time(s).');
        }

        self::fail('Chubbyphp\Tests\Mock\SampleInterface::sample(\'argument1\', true) was not expected to be called.');
    }

    public function testInterfaceWithAdditionalCall()
    {
        $argument1 = 'argument1';
        $return = 'return';

        /** @var SampleInterface|MockObject $mock */
        $mock = $this->getMockByCalls(SampleInterface::class, [
            Call::create('sample')->with($argument1, true)->willReturn($return),
        ]);

        self::assertSame($return, $mock->sample($argument1));

        try {
            $mock->sample($argument1);
        } catch (AssertionFailedError $e) {
            self::assertStringStartsWith(
                'Additional call at index 1 on class "Chubbyphp\Tests\Mock\SampleInterface"'.PHP_EOL.'[',
                $e->getMessage()
            );

            self::assertRegExp('/'.(new \ReflectionObject($mock))->getShortName().'/', $e->getMessage());

            self::assertStringEndsWith(
                ']',
                $e->getMessage()
            );

            return;
        }

        self::fail(sprintf('Expected "%s"', AssertionFailedError::class));
    }

    public function testInterfaceWithWrongCall()
    {
        /** @var SampleInterface|MockObject $mock */
        $mock = $this->getMockByCalls(SampleInterface::class, [
            Call::create('sample1')->with('argument1', true),
        ]);

        try {
            $mock->sample('argument1');
        } catch (AssertionFailedError $e) {
            self::assertStringStartsWith(
                'Call at index 0 on class "Chubbyphp\Tests\Mock\SampleInterface" expected method "sample1"'
                    .', "sample" given'.PHP_EOL.'[',
                $e->getMessage()
            );

            self::assertRegExp('/'.(new \ReflectionObject($mock))->getShortName().'/', $e->getMessage());

            self::assertStringEndsWith(
                ']',
                $e->getMessage()
            );

            return;
        }

        self::fail(sprintf('Expected "%s"', AssertionFailedError::class));
    }
}

class SampleClass implements SampleInterface
{
    public function __construct()
    {
        TestCase::fail('Construct should be mocked');
    }

    public function __clone()
    {
        TestCase::fail('Clone should be mocked');
    }

    /**
     * @param mixed $argument1
     * @param bool  $argument2
     */
    public function sample($argument1, bool $argument2 = true)
    {
    }

    public function dotNotProxy()
    {
    }
}

abstract class AbstractSampleClass implements SampleInterface
{
    public function __construct()
    {
        TestCase::fail('Construct should be mocked');
    }

    public function __clone()
    {
        TestCase::fail('Clone should be mocked');
    }

    /**
     * @param mixed $argument1
     * @param bool  $argument2
     */
    public function sample($argument1, bool $argument2 = true)
    {
    }
}

interface SampleInterface
{
    /**
     * @param mixed $argument1
     * @param bool  $argument2
     */
    public function sample($argument1, bool $argument2 = true);
}

interface AdditionalSampleInterface
{
    /**
     * @param mixed $argument1
     * @param bool  $argument2
     */
    public function additionalSample($argument1, bool $argument2 = true);
}
