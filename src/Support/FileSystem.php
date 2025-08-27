<?php
namespace ArtPulse\Support;

class FileSystem
{
    /**
     * Delete a file if it exists.
     */
    public static function safe_unlink(string $path): void
    {
        if (is_file($path)) {
            @unlink($path);
        }
    }

    /**
     * Recursively remove a directory if it exists.
     */
    public static function rm_rf(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }
        @rmdir($path);
    }
}
