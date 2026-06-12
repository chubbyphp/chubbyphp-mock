#!/usr/bin/env php
<?php

/**
 * Standalone script (PHP >= 8.3).
 *
 * - Searches for all classes and interfaces within vendor/symfony/symfony/*
 * - For each candidate it generates a stub class which implements / extends
 *   ALL public (and abstract) methods and tries to instantiate it. The stub
 *   only relies on PHP language rules, intentionally NOT on the logic of the
 *   mock library itself, so the result is usable as independent test data.
 * - Every candidate that could be instantiated is printed to STDOUT as:
 *       '\Symfony\Component\Translation\Loader\ArrayLoader' => ['\Symfony\Component\Translation\Loader\ArrayLoader'],
 * - Excluded are candidates where mocking every public method is impossible:
 *   - classes which contain static methods only
 *   - final classes
 *   - classes with at least one final public method (e.g. everything
 *     extending \Exception inherits its final getMessage(), getCode(), ...)
 *   - classes with a final __construct() / __destruct()
 *   - \Throwable based interfaces (getMessage(), ... can only be provided by
 *     the final implementations within \Exception / \Error)
 *   - \Traversable based interfaces which do not extend \Iterator or
 *     \IteratorAggregate (cannot be implemented directly)
 *   - interfaces based on \UnitEnum or \DateTimeInterface
 *   - enums and traits
 *
 * Implementation note: an incompatible stub signature triggers an uncatchable
 * E_COMPILE_ERROR. Therefore the actual probing happens in worker
 * subprocesses (chunks of classes). If a worker crashes, the master marks the
 * crashing class as failed and continues with the remaining ones.
 *
 * Usage:
 *   php generate-symfony-class-list.php > list.php
 */

declare(strict_types=1);

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

if (\PHP_VERSION_ID < 80300) {
    fwrite(STDERR, 'This script requires PHP >= 8.3, running: '.\PHP_VERSION.\PHP_EOL);
    exit(1);
}

$autoload = __DIR__.'/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, 'Missing '.$autoload.', please run "composer install" first.'.\PHP_EOL);
    exit(1);
}

require $autoload;

if ('--worker' === ($argv[1] ?? '')) {
    workerMain();
} else {
    masterMain(__FILE__);
}

exit(0);

// ---------------------------------------------------------------------------
// master
// ---------------------------------------------------------------------------

function masterMain(string $script): void
{
    $symfonyDir = __DIR__.'/vendor/symfony/symfony';

    if (!is_dir($symfonyDir)) {
        fwrite(STDERR, 'Missing directory: '.$symfonyDir.\PHP_EOL);
        exit(1);
    }

    $pending = discoverClasses($symfonyDir);
    sort($pending);

    $total = count($pending);

    fwrite(STDERR, sprintf('Found %d class/interface candidates.%s', $total, \PHP_EOL));

    $ok = [];
    $failed = [];
    $processed = 0;

    while ([] !== $pending) {
        $chunk = array_splice($pending, 0, 100);

        [$results, $crashed] = runWorker($script, $chunk);

        foreach ($results as $fqcn => $reason) {
            if (null === $reason) {
                $ok[] = $fqcn;
            } else {
                $failed[$fqcn] = $reason;
            }
        }

        if (null !== $crashed) {
            $failed[$crashed] = 'worker crashed (fatal error while creating the stub)';
        }

        // re-queue the classes of this chunk which the crashed worker never reached
        $unprocessed = array_values(array_filter(
            $chunk,
            static fn (string $fqcn): bool => !array_key_exists($fqcn, $results) && $fqcn !== $crashed,
        ));

        if ([] !== $unprocessed) {
            $pending = [...$unprocessed, ...$pending];
        }

        $processed = $total - count($pending);

        fwrite(STDERR, sprintf("\rprocessed %d/%d, instantiable: %d", $processed, $total, count($ok)));
    }

    fwrite(STDERR, \PHP_EOL);

    sort($ok);

    foreach ($ok as $fqcn) {
        echo sprintf("'\\%s' => ['\\%s'],%s", $fqcn, $fqcn, \PHP_EOL);
    }

    fwrite(STDERR, sprintf(
        'Done: %d instantiable, %d skipped/failed.%s',
        count($ok),
        count($failed),
        \PHP_EOL,
    ));

    // dump the skip reasons with: DEBUG=1 php generate-symfony-class-list.php > /dev/null
    if ('' !== (string) getenv('DEBUG')) {
        ksort($failed);
        foreach ($failed as $fqcn => $reason) {
            fwrite(STDERR, sprintf('skipped %s: %s%s', $fqcn, $reason, \PHP_EOL));
        }
    }
}

/**
 * @param list<string> $chunk
 *
 * @return array{0: array<string, string|null>, 1: string|null} results (fqcn => null|failReason) and crashed fqcn
 */
function runWorker(string $script, array $chunk): array
{
    $process = proc_open(
        [\PHP_BINARY, '-d', 'memory_limit=1G', $script, '--worker'],
        [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['file', '/dev/null', 'a']],
        $pipes,
    );

    if (!\is_resource($process)) {
        fwrite(STDERR, 'Unable to start worker process.'.\PHP_EOL);
        exit(1);
    }

    fwrite($pipes[0], implode("\n", $chunk)."\n");
    fclose($pipes[0]);

    /** @var array<string, string|null> $results */
    $results = [];
    $begun = null;

    while (false !== ($line = fgets($pipes[1]))) {
        $line = rtrim($line, "\n");

        if (str_starts_with($line, 'B ')) {
            $begun = substr($line, 2);
        } elseif (str_starts_with($line, 'O ')) {
            $results[substr($line, 2)] = null;
            $begun = null;
        } elseif (str_starts_with($line, 'F ')) {
            $parts = explode(' ', substr($line, 2), 2);
            $results[$parts[0]] = $parts[1] ?? 'unknown';
            $begun = null;
        }
    }

    fclose($pipes[1]);
    proc_close($process);

    return [$results, $begun];
}

/**
 * @return list<string>
 */
function discoverClasses(string $dir): array
{
    /** @var array<string, true> $found */
    $found = [];

    $iterator = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
    );

    /** @var \SplFileInfo $file */
    foreach ($iterator as $file) {
        if ('php' !== $file->getExtension()) {
            continue;
        }

        $path = str_replace('\\', '/', $file->getPathname());

        // skip test code and resources (fixtures, stubs, skeletons)
        if (preg_match('#/(Tests|Fixtures|Resources)/#', $path)) {
            continue;
        }

        $code = file_get_contents($file->getPathname());

        if (false === $code) {
            continue;
        }

        foreach (extractDeclarations($code) as $fqcn) {
            if (str_starts_with($fqcn, 'Symfony\\')) {
                $found[$fqcn] = true;
            }
        }
    }

    return array_keys($found);
}

/**
 * @return list<string> fully qualified class/interface names declared within the given code
 */
function extractDeclarations(string $code): array
{
    $tokens = array_values(array_filter(
        \PhpToken::tokenize($code),
        static fn (\PhpToken $token): bool => !$token->isIgnorable(),
    ));

    $namespace = '';
    $declarations = [];

    $count = count($tokens);

    for ($i = 0; $i < $count; ++$i) {
        $token = $tokens[$i];

        if ($token->is(\T_NAMESPACE)) {
            $next = $tokens[$i + 1] ?? null;
            if (null !== $next && $next->is([\T_STRING, \T_NAME_QUALIFIED])) {
                $namespace = $next->text;
            }

            continue;
        }

        if (!$token->is([\T_CLASS, \T_INTERFACE])) {
            continue;
        }

        $prev = $tokens[$i - 1] ?? null;

        // skip Foo::class and anonymous classes (new class / new readonly class)
        if (null !== $prev && $prev->is([\T_DOUBLE_COLON, \T_NEW])) {
            continue;
        }

        if (null !== $prev && $prev->is(\T_READONLY) && ($tokens[$i - 2] ?? null)?->is(\T_NEW)) {
            continue;
        }

        $next = $tokens[$i + 1] ?? null;

        if (null === $next || !$next->is(\T_STRING)) {
            continue;
        }

        $declarations[] = ('' !== $namespace ? $namespace.'\\' : '').$next->text;
    }

    return $declarations;
}

// ---------------------------------------------------------------------------
// worker
// ---------------------------------------------------------------------------

function workerMain(): void
{
    ini_set('display_errors', 'stderr');

    $counter = 0;

    while (false !== ($line = fgets(\STDIN))) {
        $fqcn = trim($line);

        if ('' === $fqcn) {
            continue;
        }

        fwrite(\STDOUT, 'B '.$fqcn."\n");

        try {
            $reason = probe($fqcn, $counter++);
        } catch (\Throwable $e) {
            $reason = $e::class.': '.$e->getMessage();
        }

        if (null === $reason) {
            fwrite(\STDOUT, 'O '.$fqcn."\n");
        } else {
            fwrite(\STDOUT, 'F '.$fqcn.' '.str_replace(["\r", "\n"], ' ', $reason)."\n");
        }
    }
}

/**
 * @return string|null null on success, otherwise the reason why the class got skipped
 */
function probe(string $fqcn, int $counter): ?string
{
    // the class might already be loaded as a side effect of a previously probed class
    // declared within the same file, but the test data has to work standalone
    $resolvable = false;

    foreach (\Composer\Autoload\ClassLoader::getRegisteredLoaders() as $loader) {
        if (false !== $loader->findFile($fqcn)) {
            $resolvable = true;

            break;
        }
    }

    if (!$resolvable) {
        return 'not autoloadable through composer on its own';
    }

    if (!class_exists($fqcn) && !interface_exists($fqcn)) {
        return 'not autoloadable';
    }

    $reflectionClass = new \ReflectionClass($fqcn);

    if ($reflectionClass->isEnum()) {
        return 'enum';
    }

    if ($reflectionClass->isTrait()) {
        return 'trait';
    }

    if ($reflectionClass->isInterface()) {
        if ($reflectionClass->implementsInterface(\UnitEnum::class)) {
            return 'enum interface, cannot be implemented by a class';
        }

        if (is_a($fqcn, \DateTimeInterface::class, true)) {
            return 'DateTimeInterface cannot be implemented by userland classes';
        }

        // \Throwable methods (getMessage(), getCode(), ...) can only be satisfied by the
        // final implementations of \Exception / \Error, so they can never be mocked
        if ($reflectionClass->implementsInterface(\Throwable::class)) {
            return 'Throwable interface, its methods can only be implemented final by \Exception / \Error';
        }

        // an interface constructor enforces its signature, a mock needs its own constructor
        if ($reflectionClass->hasMethod('__construct')) {
            return 'interface declares __construct(), a mock cannot replace it';
        }

        // \Traversable can only be implemented through \Iterator or \IteratorAggregate,
        // a plain "implements" of such an interface is impossible
        if (
            $reflectionClass->implementsInterface(\Traversable::class)
            && !$reflectionClass->implementsInterface(\Iterator::class)
            && !$reflectionClass->implementsInterface(\IteratorAggregate::class)
        ) {
            return 'Traversable interface, cannot be implemented without \Iterator or \IteratorAggregate';
        }
    } else {
        if ($reflectionClass->isFinal()) {
            return 'final class, cannot be extended';
        }

        // a mock has to replace the constructor and silence the destructor
        foreach (['__construct', '__destruct'] as $lifecycleMethodName) {
            if (
                $reflectionClass->hasMethod($lifecycleMethodName)
                && $reflectionClass->getMethod($lifecycleMethodName)->isFinal()
            ) {
                return 'final '.$lifecycleMethodName.'(), cannot be replaced';
            }
        }

        // constructors declared abstract or within an interface enforce their signature
        // (no LSP exemption), so a mock cannot replace them with its own constructor
        $constructor = $reflectionClass->getConstructor();

        if (null !== $constructor) {
            if ($constructor->isAbstract()) {
                return 'abstract __construct(), signature is enforced, a mock cannot replace it';
            }

            try {
                $prototype = $constructor->getPrototype();

                return sprintf(
                    '__construct() signature is enforced by %s, a mock cannot replace it',
                    $prototype->getDeclaringClass()->getName(),
                );
            } catch (\ReflectionException) {
                // no prototype: constructor is exempt from signature checks
            }
        }

        // mocking every public method is impossible if one of them is final
        foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isFinal() && !$method->isConstructor() && !$method->isDestructor()) {
                return sprintf(
                    'final public method %s::%s(), cannot be mocked',
                    $method->getDeclaringClass()->getName(),
                    $method->getName(),
                );
            }
        }
    }

    $publicMethods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);

    if ([] !== $publicMethods) {
        $nonStatic = array_filter($publicMethods, static fn (\ReflectionMethod $m): bool => !$m->isStatic());

        if ([] === $nonStatic) {
            return 'contains static methods only';
        }
    }

    $stubClassName = 'Probe_'.$counter.'_'.str_replace('\\', '_', $fqcn);

    $code = buildStubClass($reflectionClass, $stubClassName);

    eval($code);

    (new \ReflectionClass($stubClassName))->newInstanceWithoutConstructor();

    return null;
}

function buildStubClass(\ReflectionClass $reflectionClass, string $stubClassName): string
{
    $extends = null;
    $implements = [];

    /** @var array<string, \ReflectionMethod> $methods */
    $methods = [];
    $extraMethodCode = [];

    if ($reflectionClass->isInterface()) {
        $implements[] = '\\'.$reflectionClass->getName();

        foreach ($reflectionClass->getMethods() as $method) {
            $methods[strtolower($method->getName())] ??= $method;
        }
    } else {
        $extends = '\\'.$reflectionClass->getName();

        foreach ($reflectionClass->getMethods() as $method) {
            // final public methods are excluded upfront within probe(), final protected ones
            // do not need to be mocked and therefore stay untouched
            if ($method->isPrivate() || $method->isFinal()) {
                continue;
            }

            // abstract methods (any visibility, static included) have to be implemented
            if ($method->isAbstract()) {
                $methods[strtolower($method->getName())] ??= $method;

                continue;
            }

            // override every public method (static ones included), like the mock library does
            if (
                $method->isPublic()
                && !$method->isConstructor()
                && !$method->isDestructor()
            ) {
                $methods[strtolower($method->getName())] ??= $method;
            }
        }

        // silence destructor side effects (e.g. Process::__destruct() calls stop()),
        // the probe object gets created without constructor
        if ($reflectionClass->hasMethod('__destruct')) {
            $destructor = $reflectionClass->getMethod('__destruct');

            if (!$destructor->isPrivate() && !$destructor->isFinal()) {
                $extraMethodCode[] = '    public function __destruct() {}';
            }
        }
    }

    $methodCode = $extraMethodCode;

    foreach ($methods as $method) {
        $methodCode[] = '    '.buildMethodStub($method);
    }

    return sprintf(
        "%sclass %s%s%s\n{\n%s\n}",
        $reflectionClass->isReadOnly() ? 'readonly ' : '',
        $stubClassName,
        null !== $extends ? ' extends '.$extends : '',
        [] !== $implements ? ' implements '.implode(', ', $implements) : '',
        implode("\n", $methodCode),
    );
}

function buildMethodStub(\ReflectionMethod $method): string
{
    $declaringClass = $method->getDeclaringClass();

    $parameters = array_map(
        static fn (\ReflectionParameter $parameter): string => buildParameter($parameter, $declaringClass),
        $method->getParameters(),
    );

    $returnType = $method->getReturnType()
        ?? ($method->hasTentativeReturnType() ? $method->getTentativeReturnType() : null);

    $returnTypeCode = '';

    if (null !== $returnType && !$method->isConstructor() && !$method->isDestructor()) {
        $returnTypeCode = ': '.typeToCode($returnType, $declaringClass);
    }

    // constructors / destructors (declared on interfaces) must not throw on probing
    $body = $method->isConstructor() || $method->isDestructor()
        ? '{}'
        : "{ throw new \\LogicException('stub'); }";

    return sprintf(
        '%s%s function %s%s(%s)%s %s',
        $method->isProtected() ? 'protected' : 'public',
        $method->isStatic() ? ' static' : '',
        $method->returnsReference() ? '&' : '',
        $method->getName(),
        implode(', ', $parameters),
        $returnTypeCode,
        $body,
    );
}

function buildParameter(\ReflectionParameter $parameter, \ReflectionClass $declaringClass): string
{
    $default = null;
    $forceNullable = false;

    if ($parameter->isDefaultValueAvailable()) {
        $default = defaultValueToCode($parameter, $declaringClass);

        if (null === $default) {
            // not reproducible (e.g. "new" in initializer): widen to nullable, which is
            // allowed for parameters (contravariance), and default to null
            $default = 'null';
            $forceNullable = true;
        }
    }

    $code = '';

    $type = $parameter->getType();

    if (null !== $type) {
        $typeCode = typeToCode($type, $declaringClass);

        if ($forceNullable && !$type->allowsNull()) {
            $typeCode = match (true) {
                $type instanceof \ReflectionIntersectionType => '('.$typeCode.')|null',
                $type instanceof \ReflectionUnionType => $typeCode.'|null',
                default => '?'.$typeCode,
            };
        }

        $code .= $typeCode.' ';
    }

    if ($parameter->isPassedByReference()) {
        $code .= '&';
    }

    if ($parameter->isVariadic()) {
        $code .= '...';
    }

    $code .= '$'.$parameter->getName();

    if (null !== $default) {
        $code .= ' = '.$default;
    }

    return $code;
}

function typeToCode(\ReflectionType $type, \ReflectionClass $declaringClass): string
{
    if ($type instanceof \ReflectionNamedType) {
        $code = namedTypeToCode($type, $declaringClass);

        $lowerName = strtolower($type->getName());

        if ($type->allowsNull() && !\in_array($lowerName, ['null', 'mixed'], true)) {
            return '?'.$code;
        }

        return $code;
    }

    if ($type instanceof \ReflectionUnionType) {
        $parts = [];

        foreach ($type->getTypes() as $innerType) {
            if ($innerType instanceof \ReflectionIntersectionType) {
                $parts[] = '('.typeToCode($innerType, $declaringClass).')';
            } else {
                /** @var \ReflectionNamedType $innerType */
                $parts[] = namedTypeToCode($innerType, $declaringClass);
            }
        }

        return implode('|', $parts);
    }

    if ($type instanceof \ReflectionIntersectionType) {
        return implode('&', array_map(
            static fn (\ReflectionType $innerType): string => namedTypeToCode($innerType, $declaringClass),
            $type->getTypes(),
        ));
    }

    throw new \LogicException('Unsupported reflection type: '.$type::class);
}

function namedTypeToCode(\ReflectionNamedType $type, \ReflectionClass $declaringClass): string
{
    $name = $type->getName();
    $lowerName = strtolower($name);

    if ('self' === $lowerName) {
        return '\\'.$declaringClass->getName();
    }

    if ('parent' === $lowerName) {
        $parent = $declaringClass->getParentClass();

        return false !== $parent ? '\\'.$parent->getName() : 'parent';
    }

    if ('static' === $lowerName) {
        return 'static';
    }

    return $type->isBuiltin() ? $lowerName : '\\'.$name;
}

function defaultValueToCode(\ReflectionParameter $parameter, \ReflectionClass $declaringClass): ?string
{
    try {
        if ($parameter->isDefaultValueConstant()) {
            $constantName = $parameter->getDefaultValueConstantName();

            if (null === $constantName) {
                return null;
            }

            if (str_starts_with($constantName, 'self::')) {
                return '\\'.$declaringClass->getName().'::'.substr($constantName, 6);
            }

            if (str_starts_with($constantName, 'parent::')) {
                $parent = $declaringClass->getParentClass();

                return false !== $parent ? '\\'.$parent->getName().'::'.substr($constantName, 8) : null;
            }

            if (str_starts_with($constantName, 'static::')) {
                return null;
            }

            return '\\'.ltrim($constantName, '\\');
        }

        return valueToCode($parameter->getDefaultValue());
    } catch (\Throwable) {
        return null;
    }
}

function valueToCode(mixed $value): ?string
{
    if ($value instanceof \UnitEnum) {
        return '\\'.$value::class.'::'.$value->name;
    }

    if (\is_object($value)) {
        return null;
    }

    if (\is_array($value)) {
        $parts = [];
        $isList = array_is_list($value);

        foreach ($value as $key => $item) {
            $itemCode = valueToCode($item);

            if (null === $itemCode) {
                return null;
            }

            $parts[] = $isList ? $itemCode : var_export($key, true).' => '.$itemCode;
        }

        return '['.implode(', ', $parts).']';
    }

    if (\is_float($value)) {
        if (is_nan($value)) {
            return '\\NAN';
        }

        if (is_infinite($value)) {
            return $value > 0 ? '\\INF' : '-\\INF';
        }
    }

    return var_export($value, true);
}
