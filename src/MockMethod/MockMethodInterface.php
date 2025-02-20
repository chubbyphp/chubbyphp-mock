<?php

declare(strict_types=1);

namespace Chubbyphp\Mock\MockMethod;

interface MockMethodInterface
{
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
    );
}
