<?php

namespace App\Helpers;

use App\Exceptions\ViewNotFound;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use View;

trait AddsViewLocation
{
    /**
     * @throws ViewNotFound
     */
    public function localView(?string $view_name = null, string $classname = self::class): string
    {
        try {
            $namespace_to_path = lcfirst(str_replace('\\', DIRECTORY_SEPARATOR, (new ReflectionClass($classname))->getNamespaceName()));
        } catch (ReflectionException $reflectionException) {
            throw new RuntimeException($reflectionException->getMessage(), 0, $reflectionException);
        }
        $path = base_path($namespace_to_path);
        View::addLocation($path);
        $view_names = self::getViewNames($path);

        if ($view_names === []) {
            throw new ViewNotFound("No views found in $path.");
        }

        if ($view_name === null) {
            return $view_names[0];
        }

        if (!in_array($view_name, $view_names, true)) {
            throw new ViewNotFound("View: '$view_name' not found in $path.");
        }

        return $view_name;
    }

    /**
     * @return string[]
     */
    public static function getViewNames(string $path): array
    {
        return self::getViewNamesRecursive($path, '');
    }

    /**
     * Recursively scan directories for view files and return them in dot notation
     *
     * @return string[]
     */
    private static function getViewNamesRecursive(string $path, string $prefix): array
    {
        $result = [];
        self::collectViewNames($path, $prefix, $result);

        return $result;
    }

    /**
     * Recursively collect view names into the result array (avoids array_merge overhead)
     */
    private static function collectViewNames(string $path, string $prefix, array &$result): void
    {
        if (!is_dir($path)) {
            return;
        }

        $items = scandir($path);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $itemPath = $path.DIRECTORY_SEPARATOR.$item;

            if (is_dir($itemPath)) {
                // Recursively scan subdirectories
                $subPrefix = $prefix === '' ? $item : $prefix.'.'.$item;
                self::collectViewNames($itemPath, $subPrefix, $result);
            } elseif (str_ends_with($item, '.blade.php')) {
                // Add view file with appropriate prefix
                $viewName = substr($item, 0, -10); // Remove '.blade.php' (10 chars)
                $result[] = $prefix === '' ? $viewName : $prefix.'.'.$viewName;
            }
        }
    }
}
