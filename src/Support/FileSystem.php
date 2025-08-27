<?php
namespace ArtPulse\Support;

class FileSystem
{
    /**
     * Delete a file if it exists.
     */
    public static function safe_unlink(string $path): bool
    {
        $path = self::normalize($path);
        if (self::is_dangerous($path) || is_link($path) || !is_file($path)) {
            return false;
        }
        return @unlink($path);
    }

    /**
     * Recursively remove a directory if it exists.
     */
    public static function rm_rf(string $path): bool
    {
        $path = self::normalize($path);
        if (self::is_dangerous($path) || is_link($path) || !file_exists($path)) {
            return false;
        }
        if (is_file($path)) {
            return self::safe_unlink($path);
        }
        if (!is_dir($path)) {
            return false;
        }

        $success = true;
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            if ($item->isLink()) {
                $success = false;
                continue;
            }
            if ($item->isDir()) {
                $success = @rmdir($item->getPathname()) && $success;
            } else {
                $success = @unlink($item->getPathname()) && $success;
            }
        }
        return @rmdir($path) && $success;
    }

    private static function normalize(string $path): string
    {
        if (function_exists('wp_normalize_path')) {
            $path = wp_normalize_path($path);
        } else {
            $path = str_replace('\\', '/', $path);
        }
        return $path;
    }

    private static function is_dangerous(string $path): bool
    {
        $p = rtrim($path, '/\\');
        if ($p === '' || $p === '.' || $p === '..') {
            return true;
        }
        if ($p === '/' || preg_match('#^[A-Za-z]:$#', $p)) {
            return true;
        }
        return false;
    }
}
