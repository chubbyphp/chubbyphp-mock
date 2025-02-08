<?php

declare(strict_types=1);

namespace Chubbyphp\Mock\MockMethod;

final class WithException implements MockMethodInterface
{
    private WithoutReturn $withoutReturn;

    /**
     * @param array<mixed> $expectedParameters
     */
    public function __construct(
        string $expectedName,
        array $expectedParameters,
        private \Throwable $exception,
        mixed $strict = true,
    ) {
        $this->withoutReturn = new WithoutReturn(
            $expectedName,
            $expectedParameters,
            $strict
        );
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
        mixed $actualParameters,
    ): mixed {
        $this->withoutReturn->mock(
            $in,
            $class,
            $object,
            $Index,
            $actualName,
            $actualParameters
        );

        throw $this->exception;
    }
}
