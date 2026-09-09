<?php

declare(strict_types=1);

namespace Chubbyphp\Mock;

final class Utils
{
    public static function replaceProjectInPath(string $path): string
    {
        $cwd = getcwd() ?: null;

        return $cwd ? str_replace($cwd, '(project)', $path) : $path;
    }

    public static function getPropertyValue(\ReflectionProperty $reflectionProperty, object $object): mixed
    {
        return $reflectionProperty->isInitialized($object)
            ? $reflectionProperty->getValue($object) : '(uninitialized)';
    }
}
