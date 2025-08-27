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
            unlink($path); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
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
            $item_path = $item->getPathname();
            if ($item->isDir()) {
                if (is_dir($item_path)) {
                    rmdir($item_path); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
                }
            } elseif (is_file($item_path)) {
                unlink($item_path); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
            }
        }
        if (is_dir($path)) {
            rmdir($path); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
        }
    }
}
