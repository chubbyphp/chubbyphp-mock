<?php

declare(strict_types=1);

namespace Chubbyphp\Mock\Exceptions;

final class ParameterMismatch extends AbstractMock
{
    public function __construct(
        string $in,
        string $class,
        int $index,
        string $methodName,
        int $parameterIndex,
        mixed $actualParameter,
        mixed $expectedParameter,
        bool $strict,
    ) {
        $this->message = json_encode([
            'exception' => self::class,
            'in' => $in,
            'class' => $class,
            'index' => $index,
            'methodName' => $methodName,
            'parameterIndex' => $parameterIndex,
            'actualParameter' => $this->getData($actualParameter),
            'expectedParameter' => $this->getData($expectedParameter),
            'strict' => $strict,
        ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        $this->code = 41273;
    }
}
