<?php

declare(strict_types=1);

namespace Chubbyphp\Mock;

use Chubbyphp\Mock\Exceptions\AdditionalMethodMocks;
use Chubbyphp\Mock\Exceptions\MissingMockMethod;
use Chubbyphp\Mock\MockMethod\MockMethodInterface;

final class MockMethods
{
    private int $actualIndex;
    private int $expectedIndex;

    /**
     * @param array<MockMethodInterface> $mockMethods
     */
    public function __construct(private string $in, private string $class, private array $mockMethods)
    {
        $this->actualIndex = -1;
        $this->expectedIndex = \count($mockMethods) - 1;
    }

    public function __destruct()
    {
        if ($this->expectedIndex > $this->actualIndex) {
            throw new AdditionalMethodMocks($this->in, $this->class, $this->actualIndex, $this->expectedIndex);
        }
    }

    /**
     * @param array<mixed> $parameters
     */
    public function mock(object $object, string $name, array $parameters)
    {
        ++$this->actualIndex;

        $methodMock = $this->mockMethods[$this->actualIndex] ?? null;

        if (null === $methodMock) {
            throw new MissingMockMethod($this->in, $this->class, $this->actualIndex);
        }

        return $methodMock->mock($this->in, $this->class, $object, $this->actualIndex, $name, $parameters);
    }
}
