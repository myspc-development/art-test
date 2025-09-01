<?php
namespace ArtPulse\IntegrationTests;

use PHPUnit\Framework\TestCase;

/**
 * Ensure no closures are written to persistent WordPress storage APIs.
 */
/**
 * @group INTEGRATION
 */
class NoClosureStorageTest extends TestCase {

	public function test_no_closure_in_persistent_storage_calls(): void {
		$pattern = '/(update_option|add_option|set_transient|wp_cache_set|update_user_meta|update_post_meta)\s*\([^;]*function\s*\(/s';
		$dirs    = array( dirname( __DIR__, 2 ) . '/src', dirname( __DIR__, 1 ) );
		foreach ( $dirs as $dir ) {
			$it = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $dir ) );
			foreach ( $it as $file ) {
				if ( ! $file->isFile() || $file->getExtension() !== 'php' ) {
					continue;
				}
				$contents = file_get_contents( $file->getPathname() );
				if ( preg_match( $pattern, $contents ) ) {
					$this->fail( 'Closure passed to storage function in ' . $file->getPathname() );
				}
			}
		}
		$this->assertTrue( true );
	}
}
