<?php

declare(strict_types=1);

namespace Chubbyphp\Mock\Exceptions;

final class ParametersCountMismatch extends AbstractMock
{
    public function __construct(
        string $in,
        string $class,
        int $index,
        string $methodName,
        int $actualParametersCount,
        int $expectedParametersCount,
    ) {
        $this->message = json_encode([
            'in' => $in,
            'class' => $class,
            'index' => $index,
            'methodName' => $methodName,
            'actualParametersCount' => $actualParametersCount,
            'expectedParametersCount' => $expectedParametersCount,
        ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        $this->code = 20470;
    }
}
