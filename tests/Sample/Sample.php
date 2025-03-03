<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Sample;

class Sample
{
    private bool $initialized;
    private ?self $previous = null;

    public function __construct(private string $name, private string $value) {}

    public function setInitialized(bool $initialized): void
    {
        $this->initialized = $initialized;
    }

    public function getInitialized(): bool
    {
        return $this->initialized;
    }

    public function setPrevious(self $previous): self
    {
        $this->previous = $previous;

        return $this;
    }

    public function getPrevious(): ?self
    {
        return $this->previous;
    }
}
