<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Sample;

class DefaultParameters
{
    public const NULL = null;
    public const BOOLEAN = true;
    public const INT = 5;
    public const FLOAT = 9.81;
    public const STRING = 'string';
    public const ARRAY = ['null' => self::NULL, 'boolean' => self::BOOLEAN, 'int' => self::INT, 'float' => self::FLOAT, 'string' => self::STRING];

    public function defaultParameters(
        // null
        null $null = null,
        null $nullWithCustomConst = self::NULL,
        // boolean
        bool $booleanTrue = true,
        bool $booleanFalse = false,
        ?bool $optionalBooleanTrue = null,
        bool $booleanWithConst = ZEND_THREAD_SAFE,
        bool $booleanWithCustomConst = self::BOOLEAN,
        // int
        int $int = 42,
        ?int $optionalInt = null,
        int $intWithConst = PHP_INT_MIN,
        int $intWithCustomConst = self::INT,
        // float
        float $float = 3.14159,
        ?float $optionalFloat = null,
        float $floatWithConst = PHP_FLOAT_MIN,
        float $floatWithCustomConst = self::FLOAT,
        // string
        string $string = 'string',
        ?string $optionalString = null,
        string $stringWithConst = PHP_EOL,
        string $stringWithCustomConst = self::STRING,
        // array
        array $array = ['null' => self::NULL, 'boolean' => self::BOOLEAN, 'int' => self::INT, 'float' => self::FLOAT, 'string' => self::STRING],
        ?array $optionalArray = null,
        array $arrayWithCustomConst = self::ARRAY,
        // iterable
        iterable $iterable = ['null' => self::NULL, 'boolean' => self::BOOLEAN, 'int' => self::INT, 'float' => self::FLOAT, 'string' => self::STRING],
        // datetime
        \DateTimeImmutable $dateTimeImmutable = new \DateTimeImmutable('2025-02-16T00:25:30+01:00', new \DateTimeZone('Europe/Zurich')),
        // iterator
        \ArrayIterator $arrayIterator = new \ArrayIterator(['null' => self::NULL, 'boolean' => self::BOOLEAN, 'int' => self::INT, 'float' => self::FLOAT, 'string' => self::STRING]),
        // sample
        Sample $sample = new Sample('name', 'value'),
    ): void {}

    public static function staticDefaultParameters(
        // null
        null $null = null,
        null $nullWithCustomConst = self::NULL,
        // boolean
        bool $booleanTrue = true,
        bool $booleanFalse = false,
        ?bool $optionalBooleanTrue = null,
        bool $booleanWithConst = ZEND_THREAD_SAFE,
        bool $booleanWithCustomConst = self::BOOLEAN,
        // int
        int $int = 42,
        ?int $optionalInt = null,
        int $intWithConst = PHP_INT_MIN,
        int $intWithCustomConst = self::INT,
        // float
        float $float = 3.14159,
        ?float $optionalFloat = null,
        float $floatWithConst = PHP_FLOAT_MIN,
        float $floatWithCustomConst = self::FLOAT,
        // string
        string $string = 'string',
        ?string $optionalString = null,
        string $stringWithConst = PHP_EOL,
        string $stringWithCustomConst = self::STRING,
        // array
        array $array = ['null' => self::NULL, 'boolean' => self::BOOLEAN, 'int' => self::INT, 'float' => self::FLOAT, 'string' => self::STRING],
        ?array $optionalArray = null,
        array $arrayWithCustomConst = self::ARRAY,
        // iterable
        iterable $iterable = ['null' => self::NULL, 'boolean' => self::BOOLEAN, 'int' => self::INT, 'float' => self::FLOAT, 'string' => self::STRING],
        // datetime
        \DateTimeImmutable $dateTimeImmutable = new \DateTimeImmutable('2025-02-16T00:25:30+01:00', new \DateTimeZone('Europe/Zurich')),
        // iterator
        \ArrayIterator $arrayIterator = new \ArrayIterator(['null' => self::NULL, 'boolean' => self::BOOLEAN, 'int' => self::INT, 'float' => self::FLOAT, 'string' => self::STRING]),
        // sample
        Sample $sample = new Sample('name', 'value'),
    ): void {}
}
