<?php
namespace ArtPulse\Frontend;

require_once __DIR__ . '/_Html.php';

namespace ArtPulse\Frontend\Tests;

use WP_UnitTestCase;
use function ArtPulse\Frontend\Html\extract_widget_ids;
use function ArtPulse\Frontend\ap_set_current_user_role;
use function ArtPulse\Frontend\ap_set_user_meta;

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 * @preserveGlobalState disabled
 */
class DashboardWidgetsByRoleTest extends WP_UnitTestCase {

	protected function setUp(): void {
		parent::setUp();
		// Swallow any accidental output (debug prints) to keep test strict.
		$this->setOutputCallback( static fn() => '' );
		// Default user
		$GLOBALS['ap_testing_current_user'] = array(
			'ID'    => 111,
			'roles' => array( 'subscriber' ),
		);
		$GLOBALS['ap_testing_user_meta']    = array();
	}

	/** @return array<string, array{0:string,1:array}> */
	public function provide_roles_and_key_widgets(): array {
		return array(
			'subscriber'    => array( 'subscriber', array( 'widget_news', 'widget_favorites' ) ),
			'contributor'   => array( 'contributor', array( 'widget_news', 'widget_favorites' ) ),
			'author'        => array( 'author', array( 'widget_news', 'widget_favorites' ) ),
			'editor'        => array( 'editor', array( 'widget_news', 'widget_favorites' ) ),
			'member'        => array( 'member', array( 'widget_news', 'widget_favorites' ) ),
			'artist'        => array( 'artist', array( 'widget_artist_feed_publisher', 'widget_my_events' ) ),
			'organization'  => array( 'organization', array( 'widget_org_event_overview', 'widget_artpulse_analytics_widget' ) ),
			'administrator' => array( 'administrator', array( 'widget_org_event_overview', 'widget_artpulse_analytics_widget' ) ),
		);
	}

	/**
	 * @dataProvider provide_roles_and_key_widgets
	 */
	public function test_role_default_layout_contains_key_widgets( string $role, array $mustContain ): void {
		ap_set_current_user_role( $role );

		$html = $this->render_dashboard_for_role( $role );

		$ids = extract_widget_ids( $html );

		foreach ( $mustContain as $w ) {
			$this->assertContains( $w, $ids, "Expected $w for role $role" );
		}

		// Ensure canonicalization & dedupe: no duplicates.
		$this->assertSame( $ids, array_values( array_unique( $ids ) ) );
		$this->assertNotContains( 'widget_news_feed', $ids, 'Aliases should be canonicalized to widget_news' );
	}

	public function test_user_meta_layout_overrides_role_default(): void {
		ap_set_current_user_role( 'member' );
		$role = 'member';
		if ( ! get_role( $role ) ) {
			add_role( $role, ucfirst( $role ) ); }
		$uid = self::factory()->user->create( array( 'role' => $role ) );
		wp_set_current_user( $uid );
		// User chooses a minimal layout
		ap_set_user_meta(
			$uid,
			'ap_dashboard_layout',
			array(
				array(
					'id'      => 'widget_my_events',
					'visible' => true,
				),
				array(
					'id'      => 'widget_favorites',
					'visible' => true,
				),
				array(
					'id'      => 'widget_news_feed',
					'visible' => true,
				), // should be canonicalized
				array(
					'id'      => 'widget_unknown',
					'visible' => true,
				),   // should be ignored by renderer
				array(
					'id'      => 'widget_my_events',
					'visible' => true,
				), // duplicate
			)
		);

		ob_start();
		\ArtPulse\Core\DashboardWidgetRegistry::render_for_role( $uid );
		$html = ob_get_clean();

		$ids = extract_widget_ids( $html );

		// Only known widgets appear, canonicalized, deduped
		$this->assertContains( 'widget_my_events', $ids );
		$this->assertContains( 'widget_favorites', $ids );
		$this->assertContains( 'widget_news', $ids );
		$this->assertNotContains( 'widget_news_feed', $ids );
		$this->assertNotContains( 'widget_unknown', $ids );
		$this->assertSame( $ids, array_values( array_unique( $ids ) ) );
	}

	/** ---------- helpers ---------- */
	private function render_dashboard_for_role( string $role ): string {
		if ( ! get_role( $role ) ) {
			add_role( $role, ucfirst( $role ) ); }
		$uid = self::factory()->user->create( array( 'role' => $role ) );
		wp_set_current_user( $uid );
		ob_start();
		\ArtPulse\Core\DashboardWidgetRegistry::render_for_role( $uid );
		return ob_get_clean();
	}
}
