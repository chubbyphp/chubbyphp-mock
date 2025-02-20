<?php

declare(strict_types=1);

namespace Chubbyphp\Mock\MockMethod;

use Chubbyphp\Mock\Exceptions\MethodNameMismatch;

final class WithCallback implements MockMethodInterface
{
    /**
     * @phpstan-var callable(mixed ...$args): mixed
     */
    private $callback;

    /**
     * @phpstan-param callable(mixed ...$args): mixed $callback
     */
    public function __construct(
        private string $expectedName,
        callable $callback,
    ) {
        $this->callback = $callback;
    }

    /**
     * @param array<mixed> $actualParameters
     */
    public function mock(
        string $in,
        string $class,
        object $object,
        int $Index,
        string $actualName,
        array $actualParameters,
    ): mixed {
        if ($actualName !== $this->expectedName) {
            throw new MethodNameMismatch(
                $in,
                $class,
                $Index,
                $actualName,
                $this->expectedName
            );
        }

        return ($this->callback)(...$actualParameters);
    }
}
