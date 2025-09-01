<?php

/**

 * @group safety

 */

final class SqlInterpolationGuardTest extends \PHPUnit\Framework\TestCase {
	/** @test */
	public function no_unprepared_selects_with_superglobals(): void {
		$dirs = array( 'src', 'includes' );
		$bad  = array();
		foreach ( $dirs as $d ) {
			$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $d ) );
			foreach ( $it as $f ) {
				if ( ! $f->isFile() || $f->getExtension() !== 'php' ) {
					continue;
				}
				$code = file_get_contents( $f->getPathname() );
				if ( preg_match(
					'/\$wpdb->(?:get_(?:var|row|col|results)|query)\s*\(\s*[\'"]\s*SELECT[\s\S]+?\$(?:GLOBALS|_[A-Z]+)/i',
					$code
				) ) {
					$bad[] = $f->getPathname();
				}
			}
		}
		$this->assertSame( array(), $bad, "Unprepared SELECT with superglobals found:\n" . implode( "\n", $bad ) );
	}
}
