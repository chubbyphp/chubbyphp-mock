<?php

declare(strict_types=1);

namespace Chubbyphp\Mock;

class Call
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var bool
     */
    private $hasWith = false;

    /**
     * @var array
     */
    private $with = [];

    /**
     * @var \Throwable|null
     */
    private $exception;

    /**
     * @var bool
     */
    private $hasReturn = false;

    /**
     * @var bool
     */
    private $returnSelf = false;

    /**
     * @var mixed
     */
    private $return;

    private function __construct()
    {
    }

    /**
     * @param string $method
     *
     * @return self
     */
    public static function create(string $method): self
    {
        $self = new self();
        $self->method = $method;

        return $self;
    }

    /**
     * @param mixed ...$with
     *
     * @return self
     */
    public function with(...$with): self
    {
        $this->hasWith = true;
        $this->with = $with;

        return $this;
    }

    /**
     * @param \Throwable $exception
     *
     * @return self
     */
    public function willThrowException(\Throwable $exception): self
    {
        if ($this->hasReturn) {
            throw new \InvalidArgumentException(sprintf('%s: There is already a return', __METHOD__));
        }

        if ($this->returnSelf) {
            throw new \InvalidArgumentException(sprintf('%s: There is already a return self', __METHOD__));
        }

        $this->exception = $exception;

        return $this;
    }

    /**
     * @param mixed $return
     *
     * @return self
     */
    public function willReturn($return): self
    {
        if (null !== $this->exception) {
            throw new \InvalidArgumentException(sprintf('%s: There is already a exception', __METHOD__));
        }

        if ($this->returnSelf) {
            throw new \InvalidArgumentException(sprintf('%s: There is already a return self', __METHOD__));
        }

        $this->hasReturn = true;
        $this->return = $return;

        return $this;
    }

    /**
     * @return self
     */
    public function willReturnSelf(): self
    {
        if (null !== $this->exception) {
            throw new \InvalidArgumentException(sprintf('%s: There is already a exception', __METHOD__));
        }

        if ($this->hasReturn) {
            throw new \InvalidArgumentException(sprintf('%s: There is already a return', __METHOD__));
        }

        $this->returnSelf = true;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return bool
     */
    public function hasWith(): bool
    {
        return $this->hasWith;
    }

    /**
     * @return bool
     */
    public function hasReturn(): bool
    {
        return $this->hasReturn;
    }

    /**
     * @return bool
     */
    public function hasReturnSelf(): bool
    {
        return $this->returnSelf;
    }

    /**
     * @return array
     */
    public function getWith(): array
    {
        return $this->with;
    }

    /**
     * @return \Throwable|null
     */
    public function getException()
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
}
