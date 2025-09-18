<?php

declare(strict_types=1);

namespace Chubbyphp\Mock\Exceptions;

final class MethodNameMismatch extends AbstractMock
{
    public function __construct(
        string $in,
        string $class,
        int $index,
        string $actualName,
        string $expectedName,
    ) {
        $this->message = json_encode([
            'in' => $in,
            'class' => $class,
            'index' => $index,
            'actualName' => $actualName,
            'expectedName' => $expectedName,
        ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        $this->code = 98990;
    }
}
