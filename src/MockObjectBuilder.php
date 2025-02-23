<?php

declare(strict_types=1);

namespace Chubbyphp\Mock;

use Chubbyphp\Mock\MockMethod\MockMethodInterface;

final class MockObjectBuilder
{
    public function __construct(private MockClassBuilder $mockClassBuilder = new MockClassBuilder()) {}

    /**
     * @template T of object
     *
     * @param class-string<T>            $className
     * @param array<MockMethodInterface> $mockMethods
     *
     * @return T
     */
    public function create(string $className, array $mockMethods): object
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 0)[0];
        $in = Utils::replaceProjectInPath($trace['file'].':'.$trace['line']);

        $mockClassName = $this->mockClassBuilder->mock($className);

        return new $mockClassName(new MockMethods($in, $className, $mockMethods));
    }
}
