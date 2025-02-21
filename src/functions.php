<?php

declare(strict_types=1);

namespace Chubbyphp\Mock;

function replaceProjectInPath(string $path): string
{
    $cwd = getcwd() ?: null;

    return $cwd ? str_replace($cwd, '(project)', $path) : $path;
}
