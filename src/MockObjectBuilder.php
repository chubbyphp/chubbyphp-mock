<?php

declare(strict_types=1);

namespace Chubbyphp\Mock;

use Chubbyphp\Mock\MockMethod\MockMethodInterface;

final class MockObjectBuilder
{
    /**
     * @param class-string               $className
     * @param array<MockMethodInterface> $mockMethods
     */
    public function create(string $className, array $mockMethods): object
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1)[0];
        $in = $trace['file'].':'.$trace['line'];

        /** @var class-string $mockClassName */
        $mockClassName = str_replace('\\', '_', $className).'_Mock';

        if (class_exists($mockClassName)) {
            return new $mockClassName(new MockMethods($in, $className, $mockMethods));
        }

        $reflectionClass = new \ReflectionClass($className);

        $mockedClassString = $this->generateMockedClassString($reflectionClass, $mockClassName);

        // echo $mockedClassString;

        // dangerous as hell!!!
        eval($mockedClassString);

        return new $mockClassName(new MockMethods($in, $className, $mockMethods));
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     * @param class-string             $mockClassName
     */
    private function generateMockedClassString(\ReflectionClass $reflectionClass, string $mockClassName): string
    {
        $class = 'final class '.$mockClassName.' '.($reflectionClass->isInterface() ? 'implements' : 'extends').' '.$reflectionClass->getName().' {'.PHP_EOL;
        $class .= '    public function __construct(private Chubbyphp\Mock\MockMethods $mockMethods) { }'.PHP_EOL;

        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $class .= '    '.$this->generateMockedMethodString($reflectionClass, $reflectionMethod).PHP_EOL;
        }

        $class .= '}';

        return $class;
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     */
    private function generateMockedMethodString(\ReflectionClass $reflectionClass, \ReflectionMethod $reflectionMethod): string
    {
        if (!$reflectionMethod->isPublic() || $reflectionMethod->isStatic() || $reflectionMethod->isConstructor()) {
            return '';
        }

        $parameters = $this->generateMockedParameterString($reflectionClass, $reflectionMethod);

        $methodName = $reflectionMethod->getName();

        $method = 'public function '.$methodName.'('.$parameters.')';

        $returnType = (string) ($reflectionMethod->getReturnType() ?? $reflectionMethod->getTentativeReturnType());

        if ($returnType) {
            $method .= ': '.$returnType;
        }

        $method .= ' { ';

        if ('void' !== $returnType) {
            $method .= 'return ';
        }

        $forwardedParameters = '';

        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $forwardedParameters .= '$'.$reflectionParameter->getName().', ';
        }

        $forwardedParameters = \strlen($forwardedParameters) > 0 ? substr($forwardedParameters, 0, -2) : '';

        $method .= '$this->mockMethods->mock($this, \''.$methodName.'\', ['.$forwardedParameters.']);';

        $method .= ' }';

        return $method;
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     */
    private function generateMockedParameterString(\ReflectionClass $reflectionClass, \ReflectionMethod $reflectionMethod): string
    {
        $search = [];
        $replace = [];
        foreach (array_keys(get_defined_constants()) as $key) {
            $search[] = $reflectionClass->getNamespaceName().'\\'.$key;
            $replace[] = $key;
        }

        $parameters = '';

        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $matches = [];

            preg_match('/^Parameter \#\d+ \[ <(required|optional)> (.+) \]$/', (string) $reflectionParameter, $matches);

            $parameters .= str_replace($search, $replace, $matches[2]).', ';
        }

        return \strlen($parameters) > 0 ? substr($parameters, 0, -2) : '';
    }
}
