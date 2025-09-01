<?php
namespace ArtPulse\Admin\Tests;

use WP_UnitTestCase;
use function ArtPulse\Tests\rm_rf;

/**
 * @group ADMIN
 */
class TemplateCopyTest extends WP_UnitTestCase {

	private string $childDir;

	public function set_up() {
		parent::set_up();
		$this->childDir = sys_get_temp_dir() . '/ap-child-' . wp_generate_password( 8, false, false );
		add_filter( 'stylesheet_directory', array( $this, 'filterStylesheet' ), 10, 3 );
	}

	public function tear_down() {
		remove_filter( 'stylesheet_directory', array( $this, 'filterStylesheet' ), 10 );
		rm_rf( $this->childDir );
		$_POST = array();
		parent::tear_down();
	}

	public function filterStylesheet( $dir ) {
		return $this->childDir;
	}

	public function test_templates_copied_to_child_theme(): void {
		$admin = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin );
		$_POST['ap_copy_templates'] = '1';
		$_REQUEST['_wpnonce']       = wp_create_nonce( 'artpulse_copy_templates' );

		do_action( 'admin_init' );

		$this->assertFileExists( $this->childDir . '/templates/salient/content-artpulse_event.php' );
		$this->assertFileExists( $this->childDir . '/single-artpulse_event.php' );
	}
}
