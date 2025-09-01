<?php
require_once __DIR__ . '/../TestHelpers.php';

namespace ArtPulse\Reporting;

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

namespace ArtPulse\Reporting\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Reporting\GrantReportBuilder;
use ArtPulse\Admin\Tests\Stub;
use function ArtPulse\Tests\safe_unlink;

/**

 * @group REPORTING

 */

class GrantReportBuilderTest extends TestCase {

	protected function setUp(): void {
		Stub::reset();
	}

	public function test_generate_pdf_creates_file(): void {
		Stub::$upload_path = sys_get_temp_dir();

		if ( ! class_exists( 'Dompdf\\Dompdf' ) ) {
			eval( 'namespace Dompdf; class Dompdf { public function loadHtml($h){} public function setPaper($p){} public function render(){} public function output(){ return "PDF"; } }' );
		}

		$path = GrantReportBuilder::generate_pdf(
			array(
				'title'   => 'Grant Report',
				'summary' => array( 'Events' => 2 ),
				'events'  => array(
					array(
						'title' => 'Show',
						'rsvps' => 10,
					),
				),
				'donors'  => array(
					array(
						'name'   => 'Alice',
						'amount' => '100',
					),
				),
			)
		);

		$this->assertNotEmpty( $path );
		$this->assertFileExists( $path );
		safe_unlink( $path );
	}
}
