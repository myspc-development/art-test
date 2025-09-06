<?php
namespace ArtPulse\Db\Tests;

use WP_UnitTestCase;
use ArtPulse\Admin\WebhookLogsPage;
use ArtPulse\Integration\WebhookManager;

require_once __DIR__ . '/../../includes/install.php';

/**

 * @group DB
 */

class WebhookLogsUiTest extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		global $wpdb;
		$table = $wpdb->prefix . 'ap_webhook_logs';
		$wpdb->query( "DROP TABLE IF EXISTS $table" );
		artpulse_create_webhook_logs_table();
	}

	public function test_render_truncates_long_response_body(): void {
		$long = str_repeat( 'X', 13000 );
		WebhookManager::insert_log_for_tests( 1, '200', $long );

		ob_start();
		WebhookLogsPage::render();
		$html = ob_get_clean();

		$this->assertStringNotContainsString( $long, $html );
		$this->assertMatchesRegularExpression( '/(&hellip;|\\.\\.\\.|…)/', $html );
	}

	public function test_filtering_by_subscription_id_uses_prepared_sql(): void {
		global $wpdb;
		$captured = null;
		$filter   = function ( $sql ) use ( &$captured ) {
			$captured = $sql;
			return $sql;
		};
		add_filter( 'query', $filter );

		$_GET['subscription_id'] = '123';
		ob_start();
		WebhookLogsPage::render();
		ob_end_clean();
		remove_filter( 'query', $filter );
		unset( $_GET['subscription_id'] );

		$this->assertIsString( $captured );
		$this->assertStringContainsString( 'WHERE subscription_id', $captured );
		$this->assertStringContainsString( '%d', $captured );
	}
}
