<?php

declare(strict_types=1);

namespace Chubbyphp\Mock\MockMethod;

final class WithReturnSelf implements MockMethodInterface
{
    private readonly WithoutReturn $withoutReturn;

    /**
     * @param array<mixed> $expectedParameters
     */
    public function __construct(
        string $expectedName,
        array $expectedParameters,
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
        int $Index,
        string $actualName,
        array $actualParameters,
    ): mixed {
        $this->withoutReturn->mock(
            $in,
            $class,
            $object,
            $Index,
            $actualName,
            $actualParameters
        );

        return $object;
    }
}
