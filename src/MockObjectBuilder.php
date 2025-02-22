<?php

declare(strict_types=1);

namespace Chubbyphp\Mock;

use Chubbyphp\Mock\MockMethod\MockMethodInterface;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

final class MockObjectBuilder
{
    /**
     * @param class-string               $className
     * @param array<MockMethodInterface> $mockMethods
     */
    public function create(string $className, array $mockMethods): object
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 0)[0];
        $in = Utils::replaceProjectInPath($trace['file'].':'.$trace['line']);

        $mockClassName = $this->mock($className);

        return new $mockClassName(new MockMethods($in, $className, $mockMethods));
    }

    /**
     * @param class-string $className
     *
     * @return class-string
     */
    public function mock(string $className): string
    {
        /** @var class-string $mockClassName */
        $mockClassName = str_replace('\\', '_', $className).'_Mock';

        if (class_exists($mockClassName)) {
            return $mockClassName;
        }

        $reflectionClass = new \ReflectionClass($className);

        $mockedClass = $this->mockClass($reflectionClass, $mockClassName);

        // echo $mockedClassString;

        // NOSONAR: This eval() is safe cause its generated code based on the original one
        eval($mockedClass);

        return $mockClassName;
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     * @param class-string             $mockClassName
     */
    private function mockClass(\ReflectionClass $reflectionClass, string $mockClassName): string
    {
        $implementsOrExtends = $reflectionClass->isInterface() ? 'implements' : 'extends';

        $methods = [
            'public function __construct(private Chubbyphp\Mock\MockMethods $mockMethods) { }'.PHP_EOL,
        ];

        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $methods[] = $this->mockMethod($reflectionClass, $reflectionMethod).PHP_EOL;
        }

        $class = "final class {$mockClassName} {$implementsOrExtends} {$reflectionClass->getName()} {".PHP_EOL;
        $class .= implode(PHP_EOL, $methods).PHP_EOL;
        $class .= '}';

        return $class;
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     */
    private function mockMethod(
        \ReflectionClass $reflectionClass,
        \ReflectionMethod $reflectionMethod
    ): string {
        if (!$reflectionMethod->isPublic() || $reflectionMethod->isConstructor()) {
            return '';
        }

        if ($reflectionMethod->isStatic()) {
            return $this->mockStaticMethod($reflectionClass, $reflectionMethod);
        }

        return $this->mockDynamicMethod($reflectionClass, $reflectionMethod);
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     */
    private function mockStaticMethod(
        \ReflectionClass $reflectionClass,
        \ReflectionMethod $reflectionMethod
    ): string {
        $parameters = $this->mockParameters($reflectionClass, $reflectionMethod);

        $methodName = $reflectionMethod->getName();

        $method = 'public static function '.$methodName.'('.$parameters.')';

        $returnType = (string) ($reflectionMethod->getReturnType() ?? $reflectionMethod->getTentativeReturnType());

        if ($returnType) {
            $method .= ': '.$this->replaceSelfWithOriginalClass($reflectionClass, $returnType);
        }

        $method .= ' { throw new \Exception(\'Static method cannot be mocked\'); }';

        return $method;
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     */
    private function mockDynamicMethod(
        \ReflectionClass $reflectionClass,
        \ReflectionMethod $reflectionMethod
    ): string {
        $parameters = $this->mockParameters($reflectionClass, $reflectionMethod);

        $methodName = $reflectionMethod->getName();

        $method = 'public function '.$methodName.'('.$parameters.')';

        $returnType = (string) ($reflectionMethod->getReturnType() ?? $reflectionMethod->getTentativeReturnType());

        if ($returnType) {
            $method .= ': '.$this->replaceSelfWithOriginalClass($reflectionClass, $returnType);
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
    private function mockParameters(
        \ReflectionClass $reflectionClass,
        \ReflectionMethod $reflectionMethod
    ): string {
        $parameters = [];

        foreach ($reflectionMethod->getParameters() as $i => $reflectionParameter) {
            $pattern = $this->parameterPattern($reflectionParameter, $i);

            $matches = [];

            preg_match($pattern, (string) $reflectionParameter, $matches);

            $type = $reflectionParameter->hasType() ?
                $this->replaceSelfWithOriginalClass($reflectionClass, (string) $reflectionParameter->getType()) :
                '';
            $default = $reflectionParameter->isDefaultValueAvailable() ?
                '= '.$this->mockDefaultParameters($reflectionClass, $matches[1]) :
                '';

            $parameters[] = trim($type.' $'.$reflectionParameter->getName().' '.$default);
        }

        return implode(', ', $parameters);
    }

    private function parameterPattern(\ReflectionParameter $reflectionParameter, int $parameterIndex): string
    {
        $requiredOrOptional = !$reflectionParameter->isOptional() ? 'required' : 'optional';
        $type = preg_quote((string) $reflectionParameter->getType());
        $variable = preg_quote('$'.$reflectionParameter->getName());

        if ($reflectionParameter->hasType() && $reflectionParameter->isDefaultValueAvailable()) {
            return "/^Parameter \\#{$parameterIndex} \\[ <{$requiredOrOptional}> {$type} {$variable} = (.+) \\]$/";
        }

        if ($reflectionParameter->hasType()) {
            return "/^Parameter \\#{$parameterIndex} \\[ <{$requiredOrOptional}> {$type} {$variable} \\]$/";
        }

        if ($reflectionParameter->isDefaultValueAvailable()) {
            return "/^Parameter \\#{$parameterIndex} \\[ <{$requiredOrOptional}> {$variable} = (.+) \\]$/";
        }

        return "/^Parameter \\#{$parameterIndex} \\[ <{$requiredOrOptional}> {$variable} \\]$/";
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     */
    private function replaceSelfWithOriginalClass(\ReflectionClass $reflectionClass, string $type): string
    {
        if ('?self' === $type) {
            return '?'.$reflectionClass->getName();
        }

        if ('self' === $type) {
            return $reflectionClass->getName();
        }

        return $type;
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     */
    private function mockDefaultParameters(\ReflectionClass $reflectionClass, string $defaultParametersCode): string
    {
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $traverser = new NodeTraverser();
        $prettyPrinter = new Standard();

        $traverser->addVisitor(new class($reflectionClass) extends NodeVisitorAbstract {
            /**
             * @param \ReflectionClass<object> $reflectionClass
             */
            public function __construct(private \ReflectionClass $reflectionClass) {}

            public function enterNode(Node $node): null
            {
                if (!($node instanceof Name && !$node->isFullyQualified())) {
                    return null;
                }

                if ('self' === $node->name) {
                    $node->name = $this->reflectionClass->getName();

                    return null;
                }

                // when global const are not added as \CONST_NAME
                if (strpos($node->name, '\\')) {
                    $parts = explode('\\', $node->name);

                    /** @var non-empty-string $name */
                    $name = $parts[\count($parts) - 1];
                    $node->name = $name;
                }

                return null;
            }
        });

        /** @var array<Stmt> $stmts */
        $stmts = $parser->parse('<?php '.$defaultParametersCode.';');

        return $this->resolveOriginalClassConsts(
            $reflectionClass,
            substr(
                $prettyPrinter->prettyPrint(
                    $traverser->traverse($stmts)
                ),
                0,
                -1
            )
        );
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     */
    private function resolveOriginalClassConsts(\ReflectionClass $reflectionClass, string $parameter): string
    {
        $classConstantMatches = [];
        preg_match_all(
            '/'.$this->originalClassConstPattern($reflectionClass).'/',
            $parameter,
            $classConstantMatches
        );

        foreach ($classConstantMatches[0] as $i => $match) {
            $parameter = str_replace(
                $match,
                $this->resolveOriginalClassConst($reflectionClass, $match),
                $parameter
            );
        }

        return $parameter;
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     */
    private function originalClassConstPattern(\ReflectionClass $reflectionClass): string
    {
        return \sprintf('%s::([a-zA-Z_][a-zA-Z0-9_]*)', preg_quote($reflectionClass->getName()));
    }

    /**
     * @template T of null|bool|float|int|string
     *
     * @param \ReflectionClass<object> $reflectionClass
     * @param array<T>|T               $value
     */
    private function resolveOriginalClassConst(
        \ReflectionClass $reflectionClass,
        null|array|bool|float|int|string $value
    ): string {
        if (null === $value) {
            return 'null';
        }

        if (\is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (\is_int($value) || \is_float($value)) {
            return (string) $value;
        }

        if (\is_string($value)) {
            $matches = [];
            if (preg_match('/^'.$this->originalClassConstPattern($reflectionClass).'$/', $value, $matches)) {
                return $this->resolveOriginalClassConst(
                    $reflectionClass,
                    $reflectionClass->getConstant($matches[1])
                );
            }

            return '\''.$value.'\'';
        }

        $items = [];
        foreach ($value as $subKey => $subValue) {
            $items[] = $this->resolveOriginalClassConst($reflectionClass, $subKey)
                .' => '.$this->resolveOriginalClassConst($reflectionClass, $subValue);
        }

        return '['.implode(', ', $items).']';
    }
}
