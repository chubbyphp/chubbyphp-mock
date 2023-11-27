<?php

declare(strict_types=1);

namespace Chubbyphp\Mock;

class Call
{
    private const ALREADY_A_EXCEPTION = '%s: There is already a exception';
    private const ALREADY_A_RETURN = '%s: There is already a return';
    private const ALREADY_A_RETURN_CALLBACK = '%s: There is already a return callback';
    private const ALREADY_A_RETURN_SELF = '%s: There is already a return self';

    private string $method;

    private bool $hasWith = false;

    /**
     * @var array<mixed>
     */
    private array $with = [];

    private ?\Throwable $exception = null;

    private bool $hasReturnSelf = false;

    private bool $hasReturn = false;

    private bool $hasReturnCallback = false;

    private mixed $return = null;

    /**
     * @var null|callable
     */
    private $returnCallback;

    private function __construct(string $method)
    {
        $this->method = $method;
    }

    public static function create(string $method): self
    {
        return new self($method);
    }

    /**
     * @param mixed ...$with
     */
    public function with(...$with): self
    {
        $this->hasWith = true;
        $this->with = $with;

        return $this;
    }

    public function willThrowException(\Throwable $exception): self
    {
        if ($this->hasReturnSelf) {
            throw new \InvalidArgumentException(sprintf(self::ALREADY_A_RETURN_SELF, __METHOD__));
        }

        if ($this->hasReturn) {
            throw new \InvalidArgumentException(sprintf(self::ALREADY_A_RETURN, __METHOD__));
        }

        if ($this->hasReturnCallback) {
            throw new \InvalidArgumentException(sprintf(self::ALREADY_A_RETURN_CALLBACK, __METHOD__));
        }

        $this->exception = $exception;

        return $this;
    }

    public function willReturnSelf(): self
    {
        if (null !== $this->exception) {
            throw new \InvalidArgumentException(sprintf(self::ALREADY_A_EXCEPTION, __METHOD__));
        }

        if ($this->hasReturn) {
            throw new \InvalidArgumentException(sprintf(self::ALREADY_A_RETURN, __METHOD__));
        }

        if ($this->hasReturnCallback) {
            throw new \InvalidArgumentException(sprintf(self::ALREADY_A_RETURN_CALLBACK, __METHOD__));
        }

        $this->hasReturnSelf = true;

        return $this;
    }

    public function willReturn(mixed $return): self
    {
        if (null !== $this->exception) {
            throw new \InvalidArgumentException(sprintf(self::ALREADY_A_EXCEPTION, __METHOD__));
        }

        if ($this->hasReturnSelf) {
            throw new \InvalidArgumentException(sprintf(self::ALREADY_A_RETURN_SELF, __METHOD__));
        }

        if ($this->hasReturnCallback) {
            throw new \InvalidArgumentException(sprintf(self::ALREADY_A_RETURN_CALLBACK, __METHOD__));
        }

        $this->hasReturn = true;
        $this->return = $return;

        return $this;
    }

    public function willReturnCallback(callable $returnCallback): self
    {
        if (null !== $this->exception) {
            throw new \InvalidArgumentException(sprintf(self::ALREADY_A_EXCEPTION, __METHOD__));
        }

        if ($this->hasReturnSelf) {
            throw new \InvalidArgumentException(sprintf(self::ALREADY_A_RETURN_SELF, __METHOD__));
        }

        if ($this->hasReturn) {
            throw new \InvalidArgumentException(sprintf(self::ALREADY_A_RETURN, __METHOD__));
        }

        $this->hasReturnCallback = true;
        $this->returnCallback = $returnCallback;

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function hasWith(): bool
    {
        return $this->hasWith;
    }

    public function hasReturnSelf(): bool
    {
        return $this->hasReturnSelf;
    }

    public function hasReturn(): bool
    {
        return $this->hasReturn;
    }

    public function hasReturnCallback(): bool
    {
        return $this->hasReturnCallback;
    }

    /**
     * @return array<mixed>
     */
    public function getWith(): array
    {
        return $this->with;
    }

    public function getException(): ?\Throwable
    {
        return $this->exception;
    }

    /**
     * @return mixed
     */
    public function getReturn()
    {
        return $this->return;
    }

    /**
     * @return mixed
     */
    public function getReturnCallback()
    {
        return $this->returnCallback;
    }
}
