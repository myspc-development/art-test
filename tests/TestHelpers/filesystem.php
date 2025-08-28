<?php
/**
 * File system helpers for tests.
 *
 * @package ArtPulse\Tests
 */

namespace ArtPulse\Tests;

use ArtPulse\Support\FileSystem;

/**
 * Delete a file if it exists.
 *
 * @param string $path Path to file.
 * @return bool Whether the file was deleted.
 */
function safe_unlink( string $path ): bool {
	return FileSystem::safe_unlink( $path );
}

/**
 * Recursively remove a directory if it exists.
 *
 * @param string $path Path to directory.
 * @return bool Whether the directory was removed.
 */
function remove_dir( string $path ): bool {
	return FileSystem::rm_rf( $path );
}

// Backwards compatible alias
function rm_rf( string $path ): bool {
	return remove_dir( $path );
}
