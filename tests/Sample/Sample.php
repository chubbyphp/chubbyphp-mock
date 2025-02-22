<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Mock\Sample;

class Sample
{
    private ?self $previous = null;

    public function __construct(private string $name, private string $value) {}

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
