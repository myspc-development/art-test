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
		@unlink( $path );
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
		if ( $item->isDir() ) {
			@rmdir( $item->getPathname() );
		} else {
			@unlink( $item->getPathname() );
		}
	}
	@rmdir( $path );
}

// Backwards compatible alias
function rm_rf( string $path ): void {
        remove_dir( $path );
}
