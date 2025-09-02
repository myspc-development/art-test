<?php
namespace ArtPulse\Reporting;

require_once __DIR__ . '/../TestHelpers.php';

if ( ! function_exists( __NAMESPACE__ . '\esc_html' ) ) {
	function esc_html( $text ) {
		return $text; }
}
if ( ! function_exists( __NAMESPACE__ . '\trailingslashit' ) ) {
	function trailingslashit( $path ) {
		return rtrim( $path, '/' ) . '/'; }
}
if ( ! function_exists( __NAMESPACE__ . '\wp_upload_dir' ) ) {
	function wp_upload_dir() {
		return array( 'path' => \ArtPulse\Admin\Tests\Stub::$upload_path ); }
}
if ( ! function_exists( __NAMESPACE__ . '\wp_generate_password' ) ) {
	function wp_generate_password( $len = 12, $special = false ) {
		return \ArtPulse\Admin\Tests\Stub::$password; }
}

namespace ArtPulse\Reporting\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Reporting\SnapshotBuilder;
use ArtPulse\Admin\Tests\Stub;
use function ArtPulse\Tests\safe_unlink;

/**

 * @group REPORTING

 */

class SnapshotBuilderTest extends TestCase {

	protected function setUp(): void {
		Stub::reset();
	}

	public function test_generate_pdf_returns_file_path(): void {
		Stub::$upload_path = sys_get_temp_dir();
		Stub::$password    = 'snap';

		if ( ! class_exists( 'Dompdf\\Dompdf' ) ) {
			eval( 'namespace Dompdf; class Dompdf { public function loadHtml($h){} public function setPaper($p){} public function render(){} public function output(){ return "PDF"; } }' );
		}

		$path = SnapshotBuilder::generate_pdf(
			array(
				'title' => 'Monthly Report',
				'data'  => array( 'RSVPs' => 25 ),
			)
		);

		$this->assertNotEmpty( $path );
		$this->assertFileExists( $path );
		safe_unlink( $path );
	}

	public function test_generate_csv_returns_file_path(): void {
		Stub::$upload_path = sys_get_temp_dir();
		Stub::$password    = 'snap';

		$path = SnapshotBuilder::generate_csv(
			array(
				'title' => 'Monthly Report',
				'data'  => array( 'RSVPs' => 25 ),
			)
		);

		$this->assertNotEmpty( $path );
		$this->assertFileExists( $path );
		safe_unlink( $path );
	}
}
