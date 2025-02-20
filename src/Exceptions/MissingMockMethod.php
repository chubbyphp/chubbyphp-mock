<?php

declare(strict_types=1);

namespace Chubbyphp\Mock\Exceptions;

final class MissingMockMethod extends AbstractMock
{
    public function __construct(
        string $in,
        string $class,
        int $index,
    ) {
        $this->message = json_encode([
            'in' => $in,
            'class' => $class,
            'index' => $index,
        ], JSON_PRETTY_PRINT);

        $this->code = 47412;
    }
}
