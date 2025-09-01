<?php
use PHPUnit\Framework\TestCase;
use ArtPulse\Support\FileSystem;

/**

 * @group PHPUNIT

 */

class FileSystemTest extends TestCase {

	private string $tmpDir;

	protected function setUp(): void {
		$this->tmpDir = sys_get_temp_dir() . '/fs_' . uniqid( '', true );
		mkdir( $this->tmpDir );
	}

	protected function tearDown(): void {
		$this->forceRemove( $this->tmpDir );
	}

	private function forceRemove( string $path ): void {
		if ( ! file_exists( $path ) ) {
			return;
		}
		$items = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $path, \FilesystemIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach ( $items as $item ) {
			$itemPath = $item->getPathname();
			if ( $item->isLink() ) {
				unlink( $itemPath );
			} elseif ( $item->isDir() ) {
				rmdir( $itemPath );
			} else {
				unlink( $itemPath );
			}
		}
		rmdir( $path );
	}

	public function test_safe_unlink_removes_file(): void {
		$file = $this->tmpDir . '/file.txt';
		file_put_contents( $file, 'x' );
		$this->assertTrue( FileSystem::safe_unlink( $file ) );
		$this->assertFalse( file_exists( $file ) );
	}

	public function test_rm_rf_removes_nested_directory(): void {
		$dir = $this->tmpDir . '/a/b';
		mkdir( $dir, 0777, true );
		file_put_contents( $dir . '/file.txt', 'x' );
		$this->assertTrue( FileSystem::rm_rf( $this->tmpDir . '/a' ) );
		$this->assertFalse( file_exists( $this->tmpDir . '/a' ) );
	}

	public function test_safe_unlink_skips_symlink(): void {
		$target = $this->tmpDir . '/target.txt';
		file_put_contents( $target, 'x' );
		$link = $this->tmpDir . '/link.txt';
		symlink( $target, $link );
		$this->assertFalse( FileSystem::safe_unlink( $link ) );
		$this->assertTrue( is_link( $link ) );
		$this->assertTrue( file_exists( $target ) );
	}

	public function test_rm_rf_skips_symlink(): void {
		$targetDir = $this->tmpDir . '/target';
		mkdir( $targetDir );
		$link = $this->tmpDir . '/linkdir';
		symlink( $targetDir, $link );
		$this->assertFalse( FileSystem::rm_rf( $link ) );
		$this->assertTrue( is_link( $link ) );
		$this->assertTrue( is_dir( $targetDir ) );
	}

	public function test_nonexistent_path_returns_false(): void {
		$this->assertFalse( FileSystem::safe_unlink( $this->tmpDir . '/missing.txt' ) );
		$this->assertFalse( FileSystem::rm_rf( $this->tmpDir . '/missingdir' ) );
	}

	public function test_root_guard_refuses_root(): void {
		$this->assertFalse( FileSystem::safe_unlink( '/' ) );
		$this->assertFalse( FileSystem::rm_rf( '/' ) );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_root_guard_refuses_abspath(): void {
		$this->assertFalse( FileSystem::safe_unlink( ABSPATH ) );
		$this->assertFalse( FileSystem::rm_rf( ABSPATH ) );
		$this->assertTrue( file_exists( ABSPATH ) );
	}
}
