<?php

declare(strict_types=1);

namespace Chubbyphp\Mock\MockMethod;

final class WithException implements MockMethodInterface // NOSONAR
{
    private readonly WithoutReturn $withoutReturn;

    /**
     * @param array<mixed> $expectedParameters
     */
    public function __construct(
        string $expectedName,
        array $expectedParameters,
        private readonly \Throwable $exception,
        bool $strict = true,
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
        int $index,
        string $actualName,
        mixed $actualParameters,
    ): mixed {
        $this->withoutReturn->mock(
            $in,
            $class,
            $object,
            $index,
            $actualName,
            $actualParameters
        );

        throw $this->exception;
    }
}
