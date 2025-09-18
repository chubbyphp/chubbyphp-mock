<?php

declare(strict_types=1);

namespace Chubbyphp\Mock\Exceptions;

final class AdditionalMethodMocks extends AbstractMock
{
    public function __construct(
        string $in,
        string $class,
        int $actualIndex,
        int $expectedIndex,
    ) {
        $this->message = json_encode([
            'in' => $in,
            'class' => $class,
            'actualIndex' => $actualIndex,
            'expectedIndex' => $expectedIndex,
        ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        $this->code = 90952;
    }
}
