<?php

declare(strict_types=1);

namespace Chubbyphp\Mock;

abstract class MethodMockException extends \RuntimeException
{
    abstract public function __toString(): string;

    final protected function getDataType(mixed $input): string
    {
        return \is_object($input) ? $input::class : \gettype($input);
    }
}

final class MethodNameMismatch extends MethodMockException
{
    public function __construct(
        private string $mockedIn,
        private string $mockedClass,
        private int $mockedMethodIndex,
        private string $actual,
        private string $expect,
    ) {}

    public function __toString(): string
    {
        $context = json_encode(['mockedIn' => $this->mockedIn,
            'mockedClass' => $this->mockedClass,
            'mockedMethodIndex' => $this->mockedMethodIndex,
            'actual' => $this->actual,
            'expect' => $this->expect,
        ], JSON_PRETTY_PRINT);

        return "Method name mismatch: {$context}";
    }
}

final class ParametersCountMismatch extends MethodMockException
{
    public function __construct(
        private string $mockedIn,
        private string $mockedClass,
        private int $mockedMethodIndex,
        private string $mockedMethodName,
        private int $actual,
        private int $expect,
    ) {}

    public function __toString(): string
    {
        $context = json_encode(['mockedIn' => $this->mockedIn,
            'mockedClass' => $this->mockedClass,
            'mockedMethodIndex' => $this->mockedMethodIndex,
            'mockedMethodName' => $this->mockedMethodName,
            'actual' => $this->actual,
            'expect' => $this->expect,
        ], JSON_PRETTY_PRINT);

        return "Parameters count mismatch: {$context}";
    }
}

final class ParameterMismatch extends MethodMockException
{
    public function __construct(
        private string $mockedIn,
        private string $mockedClass,
        private int $mockedMethodIndex,
        private string $mockedMethodName,
        private int $mockedParameterIndex,
        private mixed $actual,
        private mixed $expect,
        private bool $strict,
    ) {}

    public function __toString(): string
    {
        $context = json_encode(['mockedIn' => $this->mockedIn,
            'mockedClass' => $this->mockedClass,
            'mockedMethodIndex' => $this->mockedMethodIndex,
            'mockedMethodName' => $this->mockedMethodName,
            'mockedParameterIndex' => $this->mockedParameterIndex,
            'actual' => $this->getDataType($this->actual),
            'expect' => $this->getDataType($this->expect),
            'strict' => $this->strict,
        ], JSON_PRETTY_PRINT);

        return "Parameter mismatch: {$context}";
    }
}

interface MockMethod
{
    public function mock(
        string $mockedIn,
        object $mockObject,
        int $mockedMethodIndex,
        string $actualName,
        mixed $actualParameters,
    );
}

final class WithoutReturn implements MockMethod
{
    public function __construct(private string $expectedName, private array $expectedParameters, private $strict = true) {}

    public function mock(
        string $mockedIn,
        object $mockObject,
        int $mockedMethodIndex,
        string $actualName,
        mixed $actualParameters,
    ): void {
        if ($actualName !== $this->expectedName) {
            throw new MethodNameMismatch(
                $mockedIn,
                \get_class($mockObject),
                $mockedMethodIndex,
                $actualName,
                $this->expectedName
            );
        }

        $this->validateParameters(
            $mockedIn,
            \get_class($mockObject),
            $mockedMethodIndex,
            $actualName,
            $actualParameters,
            $this->expectedParameters,
            $this->strict,
        );
    }

    private function validateParameters(
        string $mockedIn,
        string $mockedClass,
        int $mockedMethodIndex,
        string $actualName,
        array $actualParameters,
        array $expectedParameters,
        bool $strict,
    ): void {
        $actualParametersCount = \count($actualParameters);
        $expectedParametersCount = \count($expectedParameters);

        if ($actualParametersCount !== $expectedParametersCount) {
            throw new ParametersCountMismatch(
                $mockedIn,
                $mockedClass,
                $mockedMethodIndex,
                $actualName,
                $actualParametersCount,
                $expectedParametersCount,
            );
        }

        $actualParameters = array_values($actualParameters);
        $expectedParameters = array_values($expectedParameters);

        foreach ($expectedParameters as $i => $expectedParameter) {
            $actualParameter = $actualParameters[$i];

            if ($strict) {
                if ($actualParameter !== $expectedParameter) {
                    throw new ParameterMismatch(
                        $mockedIn,
                        $mockedClass,
                        $mockedMethodIndex,
                        $actualName,
                        $i,
                        $actualParameter,
                        $expectedParameter,
                        true,
                    );
                }
            } else {
                if ($actualParameter !== $expectedParameter) {
                    throw new ParameterMismatch(
                        $mockedIn,
                        $mockedClass,
                        $mockedMethodIndex,
                        $actualName,
                        $i,
                        $actualParameter,
                        $expectedParameter,
                        false,
                    );
                }
            }
        }
    }
}

final class WithReturn implements MockMethod
{
    private WithoutReturn $withoutReturn;

    public function __construct(
        string $expectedName,
        array $expectedParameters,
        private mixed $return,
        $strict = true,
    ) {
        $this->withoutReturn = new WithoutReturn($expectedName, $expectedParameters, $strict);
    }

    public function mock(
        string $mockedIn,
        object $mockObject,
        int $mockedMethodIndex,
        string $actualName,
        mixed $actualParameters
    ): mixed {
        $this->withoutReturn->mock(
            $mockedIn,
            $mockObject,
            $mockedMethodIndex,
            $actualName,
            $actualParameters
        );

        return $this->return;
    }
}

final class WithReturnSelf implements MockMethod
{
    private WithoutReturn $withoutReturn;

    public function __construct(
        string $expectedName,
        array $expectedParameters,
        $strict = true,
    ) {
        $this->withoutReturn = new WithoutReturn(
            $expectedName,
            $expectedParameters,
            $strict
        );
    }

    public function mock(
        string $mockedIn,
        object $mockObject,
        int $mockedMethodIndex,
        string $actualName,
        mixed $actualParameters
    ): mixed {
        $this->withoutReturn->mock(
            $mockedIn,
            $mockObject,
            $mockedMethodIndex,
            $actualName,
            $actualParameters
        );

        return $mockObject;
    }
}

final class WithException implements MockMethod
{
    private WithoutReturn $withoutReturn;

    public function __construct(
        string $expectedName,
        array $expectedParameters,
        private \Exception $exception,
        $strict = true,
    ) {
        $this->withoutReturn = new WithoutReturn(
            $expectedName,
            $expectedParameters,
            $strict
        );
    }

    public function mock(
        string $mockedIn,
        object $mockObject,
        int $mockedMethodIndex,
        string $actualName,
        mixed $actualParameters
    ): mixed {
        $this->withoutReturn->mock(
            $mockedIn,
            $mockObject,
            $mockedMethodIndex,
            $actualName,
            $actualParameters
        );

        throw $this->exception;
    }
}

final class WithCallback implements MockMethod
{
    /**
     * @var callable(
     *   string $mockedIn,
     *   object $mockObject,
     *   int $mockedMethodIndex,
     *   string $actualName,
     *   mixed $actualParameters
     * ): mixed
     */
    private $callback;

    /**
     * @param callable(
     *   string $mockedIn,
     *   object $mockObject,
     *   int $mockedMethodIndex,
     *   string $actualName,
     *   mixed $actualParameters
     * ): mixed $callback
     */
    public function __construct(
        private string $expectedName,
        callable $callback,
    ) {
        $this->callback = $callback;
    }

    public function mock(
        string $mockedIn,
        object $mockObject,
        int $mockedMethodIndex,
        string $actualName,
        mixed $actualParameters,
    ): mixed {
        if ($actualName !== $this->expectedName) {
            throw new MethodNameMismatch(
                $mockedIn,
                \get_class($mockObject),
                $mockedMethodIndex,
                $actualName,
                $this->expectedName
            );
        }

        return ($this->callback)(
            $mockedIn,
            $mockObject,
            $mockedMethodIndex,
            $actualName,
            $actualParameters
        );
    }
}

final class MockObjectBuilder
{
    /**
     * @param class-string      $class
     * @param array<MethodMock> $methodMocks
     */
    public function create(string $class, array $methodMocks): object
    {
        // class generation and instantiation
    }
}

$mockObjectBuilder = new MockObjectBuilder();

$mockObjectBuilder->create(DateTimeService::class, [
    new WithReturn(
        'format',
        [new \DateTimeImmutable('2004-02-12T15:19:21+00:00'), 'c'],
        '2004-02-12T15:19:21+00:00',
        false
    ),
    new WithCallback(
        'format',
        static function (
            string $mockedIn,
            object $mockObject,
            int $mockedMethodIndex,
            string $actualName,
            mixed $actualParameters,
        ) {
            // to what ever you want

            return '2004-02-12T15:19:21+00:00';
        },
        false
    ),
]);

final class DateTimeService
{
    public function format(\DateTimeImmutable $dateTime, string $format): string
    {
        return '';
    }
}
