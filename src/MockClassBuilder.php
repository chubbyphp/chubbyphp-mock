<?php

declare(strict_types=1);

namespace Chubbyphp\Mock;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

final class MockClassBuilder
{
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

        // echo $mockedClass;

        eval($mockedClass); // NOSONAR

        return $mockClassName;
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     * @param class-string             $mockClassName
     */
    private function mockClass(\ReflectionClass $reflectionClass, string $mockClassName): string
    {
        $reflectionClasses = $this->resolveReflectionClasses($reflectionClass);

        $implementsOrExtends = $reflectionClass->isInterface() ? 'implements' : 'extends';

        $methods = [
            'public function __construct(private Chubbyphp\Mock\MockMethods $mockMethods) { }'.PHP_EOL,
            'public function __destruct() { }'.PHP_EOL,
        ];

        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $methods[] = $this->mockMethod($reflectionClasses, $reflectionMethod).PHP_EOL;
        }

        $class = "final class {$mockClassName} {$implementsOrExtends} {$reflectionClass->getName()} {".PHP_EOL;
        $class .= implode(PHP_EOL, $methods).PHP_EOL;
        $class .= '}';

        return $class;
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     *
     * @return non-empty-array<\ReflectionClass<object>>
     */
    private static function resolveReflectionClasses(\ReflectionClass $reflectionClass): array
    {
        if ($reflectionClass->isInterface()) {
            return [$reflectionClass];
        }

        $reflectionClasses = [];

        do {
            $reflectionClasses[] = $reflectionClass;
        } while (false !== $reflectionClass = $reflectionClass->getParentClass());

        return $reflectionClasses;
    }

    /**
     * @param non-empty-array<int, \ReflectionClass<object>> $reflectionClasses
     */
    private function mockMethod(
        array $reflectionClasses,
        \ReflectionMethod $reflectionMethod
    ): string {
        if (
            $reflectionMethod->isPrivate()
            || ($reflectionMethod->isProtected() && !$reflectionMethod->isAbstract())
            || $reflectionMethod->isConstructor()
            || $reflectionMethod->isDestructor()
        ) {
            return '';
        }

        if ($reflectionMethod->isStatic()) {
            return $this->mockStaticMethod($reflectionClasses, $reflectionMethod);
        }

        return $this->mockDynamicMethod($reflectionClasses, $reflectionMethod);
    }

    /**
     * @param non-empty-array<int, \ReflectionClass<object>> $reflectionClasses
     */
    private function mockStaticMethod(
        array $reflectionClasses,
        \ReflectionMethod $reflectionMethod
    ): string {
        $parameters = $this->mockParameters($reflectionClasses, $reflectionMethod);

        $methodName = $reflectionMethod->getName();

        $visibility = $reflectionMethod->isProtected() ? 'protected' : 'public';
        $returnsReference = $reflectionMethod->returnsReference() ? '&' : '';

        $method = $visibility.' static function '.$returnsReference.$methodName.'('.$parameters.')';

        $returnType = (string) ($reflectionMethod->getReturnType() ?? $reflectionMethod->getTentativeReturnType());

        if ($returnType) {
            $method .= ': '.$this->replaceSelfWithOriginalClassInType($reflectionClasses, $reflectionMethod->getName(), $returnType);
        }

        $method .= ' { throw new \Exception(\'Static method cannot be mocked\'); }';

        return $method;
    }

    /**
     * @param non-empty-array<int, \ReflectionClass<object>> $reflectionClasses
     */
    private function mockDynamicMethod(
        array $reflectionClasses,
        \ReflectionMethod $reflectionMethod
    ): string {
        $parameters = $this->mockParameters($reflectionClasses, $reflectionMethod);

        $methodName = $reflectionMethod->getName();

        $visibility = $reflectionMethod->isProtected() ? 'protected' : 'public';
        $returnsReference = $reflectionMethod->returnsReference() ? '&' : '';

        $method = $visibility.' function '.$returnsReference.$methodName.'('.$parameters.')';

        $returnType = (string) ($reflectionMethod->getReturnType() ?? $reflectionMethod->getTentativeReturnType());

        if ($returnType) {
            $method .= ': '.$this->replaceSelfWithOriginalClassInType($reflectionClasses, $reflectionMethod->getName(), $returnType);
        }

        $method .= ' { ';

        if ('void' !== $returnType && 'never' !== $returnType) {
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
     * @param non-empty-array<int, \ReflectionClass<object>> $reflectionClasses
     */
    private function mockParameters(
        array $reflectionClasses,
        \ReflectionMethod $reflectionMethod
    ): string {
        $parameters = [];

        foreach ($reflectionMethod->getParameters() as $i => $reflectionParameter) {
            $pattern = $this->parameterPattern($reflectionParameter, $i);

            $matches = [];

            preg_match($pattern, (string) $reflectionParameter, $matches);

            $type = $reflectionParameter->hasType()
                ? $this->replaceSelfWithOriginalClassInType($reflectionClasses, $reflectionMethod->getName(), (string) $reflectionParameter->getType())
                : '';
            $variable = $matches[1];
            $default = $matches[2]
                ? '= '.$this->mockDefaultParameters($reflectionClasses, $reflectionMethod->getName(), $matches[2])
                : '';

            $parameter = trim($type.' '.$variable.' '.$default);

            $parameters[] = $parameter;
        }

        return implode(', ', $parameters);
    }

    private function parameterPattern(\ReflectionParameter $reflectionParameter, int $parameterIndex): string
    {
        $requiredOrOptional = !$reflectionParameter->isOptional() ? 'required' : 'optional';
        $byReference = preg_quote($reflectionParameter->isPassedByReference() ? '&' : '');
        $variadic = preg_quote($reflectionParameter->isVariadic() ? '...' : '');
        $variable = preg_quote('$'.$reflectionParameter->getName());

        if ($reflectionParameter->hasType()) {
            return "/^Parameter \\#{$parameterIndex} \\[ <{$requiredOrOptional}> [^ ]+ ({$byReference}{$variadic}{$variable})(.*) \\]$/";
        }

        return "/^Parameter \\#{$parameterIndex} \\[ <{$requiredOrOptional}> ({$byReference}{$variadic}{$variable})(.*) \\]$/";
    }

    /**
     * @param non-empty-array<int, \ReflectionClass<object>> $reflectionClasses
     */
    private function replaceSelfWithOriginalClassInType(array $reflectionClasses, string $methodName, string $type): string
    {
        $isNullable = false;

        if ('?' === $type[0]) {
            $isNullable = true;
            $type = substr($type, 1);
        }

        $typeParts = explode('|', $type);

        foreach ($typeParts as $i => $typePart) {
            if ('self' === $typePart) {
                $selfReflectionClass = $this->resolveSelfReflectionClassForMethodName($reflectionClasses, $methodName);

                $typeParts[$i] = $selfReflectionClass->getName();
            } elseif ('parent' === $typePart) {
                /** @var \ReflectionClass<object> $parentReflectionClass */
                $parentReflectionClass = $this->resolveSelfReflectionClassForMethodName($reflectionClasses, $methodName)->getParentClass();

                $typeParts[$i] = $parentReflectionClass->getName();
            }
        }

        $type = implode('|', $typeParts);

        return $isNullable ? '?'.$type : $type;
    }

    /**
     * @param non-empty-array<int, \ReflectionClass<object>> $reflectionClasses
     *
     * @return \ReflectionClass<object>
     */
    private function resolveSelfReflectionClassForMethodName(array $reflectionClasses, string $methodName): \ReflectionClass
    {
        foreach (array_reverse(\array_slice($reflectionClasses, 1)) as $reflectionClass) {
            if ($reflectionClass->hasMethod($methodName)) {
                return $reflectionClass;
            }
        }

        return $reflectionClasses[0];
    }

    /**
     * @param non-empty-array<int, \ReflectionClass<object>> $reflectionClasses
     */
    private function mockDefaultParameters(array $reflectionClasses, string $methodName, string $defaultParametersCode): string
    {
        // drop the ' = ' at the beginning
        $defaultParametersCode = substr($defaultParametersCode, 3);

        if ('<default>' === $defaultParametersCode) {
            // @codeCoverageIgnoreStart
            return 'null';
            // @codeCoverageIgnoreEnd
        }

        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $traverser = new NodeTraverser();
        $prettyPrinter = new Standard();

        $traverser->addVisitor(new class($this, $reflectionClasses, $methodName) extends NodeVisitorAbstract {
            /**
             * @param non-empty-array<int, \ReflectionClass<object>> $reflectionClasses
             */
            public function __construct(private MockClassBuilder $mockClassBuilder, private array $reflectionClasses, private string $methodName) {}

            public function enterNode(Node $node): null
            {
                if (!($node instanceof Name && !$node->isFullyQualified())) {
                    return null;
                }

                if ('self' === $node->name) {
                    $reflectionMethod = new \ReflectionMethod($this->mockClassBuilder, 'replaceSelfWithOriginalClassInType');

                    /** @var non-empty-string */
                    $name = $reflectionMethod->invoke($this->mockClassBuilder, $this->reflectionClasses, $this->methodName, 'self');
                    $node->name = $name;

                    return null;
                }

                if ('parent' === $node->name) {
                    $reflectionMethod = new \ReflectionMethod($this->mockClassBuilder, 'replaceSelfWithOriginalClassInType');

                    /** @var non-empty-string */
                    $name = $reflectionMethod->invoke($this->mockClassBuilder, $this->reflectionClasses, $this->methodName, 'parent');
                    $node->name = $name;

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
            $reflectionClasses[0],
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
        array|bool|float|int|string|null $value
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
                /** @var null|bool|float|int|string */
                $constantValue = $reflectionClass->getConstant($matches[1]);

                return $this->resolveOriginalClassConst(
                    $reflectionClass,
                    $constantValue
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
