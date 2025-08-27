<?php
/**
 * File system helpers for tests.
 *
 * @package ArtPulse\Tests
 */

namespace ArtPulse\Tests;

/**
 * Delete a file if it exists.
 *
 * @param string $path Path to file.
 * @return void
 */
function safe_unlink( string $path ): void {
        if ( is_file( $path ) ) {
                unlink( $path );
        }
}

/**
 * Recursively remove a directory if it exists.
 *
 * @param string $path Path to directory.
 * @return void
 */
function remove_dir( string $path ): void {
        if ( ! is_dir( $path ) ) {
                return;
        }
        $items = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator( $path, \FilesystemIterator::SKIP_DOTS ),
                \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ( $items as $item ) {
                $item_path = $item->getPathname();
                if ( $item->isDir() ) {
                        if ( is_dir( $item_path ) ) {
                                rmdir( $item_path );
                        }
                } elseif ( is_file( $item_path ) ) {
                        unlink( $item_path );
                }
        }
        if ( is_dir( $path ) ) {
                rmdir( $path );
        }
}

// Backwards compatible alias
function rm_rf( string $path ): void {
        remove_dir( $path );
}
