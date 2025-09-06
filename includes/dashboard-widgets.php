<?php
/**
 * Dashboard widget helpers and AJAX handlers.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetManager;
use ArtPulse\Frontend\DashboardCard;
use ArtPulse\Dashboard\WidgetVisibilityManager;

function ap_render_widget( string $widget_id, ?int $user_id = null ): void {
	$uid    = $user_id ?? get_current_user_id();
	$config = DashboardWidgetRegistry::get_widget( $widget_id, $uid );
	if ( ! $config ) {
		return;
	}

	$lazy = apply_filters( 'ap_dashboard_widget_lazy', $config['lazy'] ?? false, $widget_id, $uid, $config );
	if ( $lazy ) {
		$nonce = wp_create_nonce( 'ap_render_widget' );
		echo '<div class="ap-widget-placeholder" data-widget-id="' . esc_attr( $widget_id ) . '" data-nonce="' . esc_attr( $nonce ) . '"><span class="spinner"></span></div>';
		return;
	}

	echo DashboardCard::render( $widget_id, $uid );
	do_action( 'ap_widget_rendered', $widget_id, $uid );
}

function register_ap_widget( string $id, array $args ): void {
	if ( isset( $args['component'] ) && ! isset( $args['callback'] ) ) {
		$args['callback'] = $args['component'];
	}
	if ( isset( $args['title'] ) && ! isset( $args['label'] ) ) {
		$args['label'] = $args['title'];
	}
	\ArtPulse\Core\DashboardWidgetRegistry::register_widget( $id, $args );
}

function ap_render_js_widget( string $id, array $props = array() ): void {
	$defaults = array(
		'apiRoot' => esc_url_raw( rest_url() ),
		'nonce'   => wp_create_nonce( 'wp_rest' ),
	);

	$props      = array_merge( $defaults, $props );
	$json_props = esc_attr( wp_json_encode( $props ) );

	echo '<div id="ap-widget-' . esc_attr( $id ) . '" data-widget="' . esc_attr( $id ) . '" data-props=\'' . $json_props . '\'></div>';
}

\ArtPulse\Core\ShortcodeRegistry::register(
	'ap_widget',
	'Dashboard Widget',
	function ( $atts ) {
		$atts = shortcode_atts(
			array(
				'id'   => '',
				'role' => '',
			),
			$atts,
			'ap_widget'
		);

		$id = sanitize_key( $atts['id'] );
		if ( ! $id ) {
			return '';
		}

		$current_role = DashboardController::get_role( get_current_user_id() );

		$config = DashboardWidgetRegistry::getById( $id );
		if ( ! $config ) {
			return '';
		}

		$widget_roles = isset( $config['roles'] ) ? (array) $config['roles'] : array();
		if ( $widget_roles && ! in_array( $current_role, $widget_roles, true ) ) {
			return '';
		}

		$attr_roles = array_filter( array_map( 'sanitize_key', explode( ',', (string) $atts['role'] ) ) );
		if ( $attr_roles && ! in_array( $current_role, $attr_roles, true ) ) {
			return '';
		}

		$rules = WidgetVisibilityManager::get_visibility_rules();
		if ( isset( $rules[ $id ] ) ) {
			$rule  = $rules[ $id ];
			$user  = wp_get_current_user();
			$roles = (array) $user->roles;
			if ( ! empty( $rule['capability'] ) && ! user_can( $user, $rule['capability'] ) ) {
				return '';
			}
			if ( ! empty( $rule['allowed_roles'] ) && empty( array_intersect( $roles, (array) $rule['allowed_roles'] ) ) ) {
				return '';
			}
			if ( ! empty( $rule['exclude_roles'] ) && array_intersect( $roles, (array) $rule['exclude_roles'] ) ) {
				return '';
			}
		}

		ob_start();
		ap_render_widget( $id );
		$html = ob_get_clean();

		if ( $html === '' ) {
			return '';
		}

		return '<div class="DashboardCard">' . $html . '</div>';
	}
);


function ap_get_all_widget_definitions( bool $include_schema = false ): array {
	return DashboardWidgetManager::getWidgetDefinitions( $include_schema );
}

/**
 * Wrapper used by front-end scripts to load widget definitions with icons.
 */
function artpulse_get_dashboard_widgets( bool $include_schema = true ): array {
	return DashboardWidgetManager::getWidgetDefinitions( $include_schema );
}


add_action( 'wp_ajax_ap_save_dashboard_widget_config', 'ap_save_dashboard_widget_config' );

add_action( 'wp_ajax_ap_save_widget_layout', 'ap_save_widget_layout' );
add_action( 'wp_ajax_ap_save_role_layout', 'ap_save_role_layout' );
add_action( 'wp_ajax_ap_save_user_layout', 'ap_save_user_layout' );
add_action( 'wp_ajax_save_widget_order', 'ap_save_widget_order' );
add_action( 'wp_ajax_ap_save_dashboard_order', 'ap_save_dashboard_order_callback' );
add_action( 'wp_ajax_ap_render_widget', 'ap_ajax_render_widget' );
add_action(
	'wp_ajax_save_dashboard_layout',
	function () {
		check_admin_referer( 'save_dashboard_layout' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}
		check_ajax_referer( 'ap_widget_nonce', 'nonce' );
		$layout = json_decode( stripslashes( $_POST['layout'] ?? '' ), true );
		if ( ! is_array( $layout ) ) {
			wp_send_json_error( 'Invalid layout' );
		}

		update_user_meta( get_current_user_id(), 'ap_dashboard_layout', $layout );
		wp_send_json_success();
	}
);

function ap_ajax_render_widget(): void {
	$widget_id = sanitize_key( $_POST['widget_id'] ?? '' );
	check_ajax_referer( 'ap_render_widget', 'nonce' );

	if ( ! $widget_id || ! is_user_logged_in() || ! current_user_can( 'read' ) ) {
		wp_send_json_error( array( 'message' => 'Invalid widget' ), 403 );
	}

	$html = DashboardCard::render( $widget_id, get_current_user_id() );
	wp_send_json_success( array( 'html' => $html ) );
}

function ap_save_dashboard_widget_config(): void {
	check_admin_referer( 'ap_save_dashboard_widget_config' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Cheatin&#8217; uh?' ) );
	}
	check_ajax_referer( 'ap_dashboard_widget_config', 'nonce' );

	$raw       = $_POST['config'] ?? array();
	$sanitized = array();
	foreach ( $raw as $role => $widgets ) {
		$role_key = sanitize_key( $role );
		$ordered  = array();
		foreach ( (array) $widgets as $w ) {
			$ordered[] = sanitize_key( $w );
		}
		$sanitized[ $role_key ] = $ordered;
	}

	update_option( 'ap_dashboard_widget_config', $sanitized );
	wp_send_json_success( array( 'saved' => true ) );
}

function ap_save_widget_layout(): void {
	check_admin_referer( 'ap_save_widget_layout' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Cheatin&#8217; uh?' ) );
	}
	$role = sanitize_key( $_POST['role'] ?? '' );
	if ( ! get_role( $role ) && ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid role', 'artpulse' ) ), 400 );
		return;
	}
	check_ajax_referer( 'ap_save_widget_layout', 'nonce' );
	if ( ! in_array( $role, wp_get_current_user()->roles, true ) && ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Permission denied', 'artpulse' ) ), 403 );
		return;
	}
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( 'Saving widget layout for user ' . get_current_user_id() );
	}

	$uid = get_current_user_id();

	if ( isset( $_POST['layout'] ) ) {
		$layout_raw = $_POST['layout'];
		if ( is_string( $layout_raw ) ) {
			$layout_raw = json_decode( $layout_raw, true );
		}
		$valid_ids = array_column( DashboardWidgetRegistry::get_definitions(), 'id' );
		$ordered   = array();
		foreach ( (array) $layout_raw as $item ) {
			if ( is_array( $item ) && isset( $item['id'] ) ) {
				$id  = sanitize_key( $item['id'] );
				$vis = isset( $item['visible'] ) ? filter_var( $item['visible'], FILTER_VALIDATE_BOOLEAN ) : true;
			} else {
				$id  = sanitize_key( $item );
				$vis = true;
			}
			if ( in_array( $id, $valid_ids, true ) ) {
				$ordered[] = array(
					'id'      => $id,
					'visible' => $vis,
				);
			}
		}
		update_user_meta( $uid, 'ap_dashboard_layout', $ordered );
	}

	wp_send_json_success( array( 'saved' => true ) );
}

function ap_save_role_layout(): void {
	check_admin_referer( 'ap_save_role_layout' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Cheatin&#8217; uh?' ) );
	}
	check_ajax_referer( 'ap_save_role_layout', 'nonce' );

	$role = sanitize_key( $_POST['role'] ?? '' );
	if ( ! get_role( $role ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid role', 'artpulse' ) ) );
		return;
	}
	$layout = $_POST['layout'] ?? array();
	if ( is_string( $layout ) ) {
		$layout = json_decode( $layout, true );
	}
	if ( ! is_array( $layout ) ) {
		$layout = array();
	}

	DashboardWidgetManager::saveRoleLayout( $role, $layout );
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( 'Saved role layout for ' . $role . ': ' . wp_json_encode( $layout ) );
	}
	wp_send_json_success( array( 'saved' => true ) );
}

function ap_save_user_layout(): void {
	// Verify request nonce
	check_ajax_referer( 'ap_save_user_layout', 'nonce' );

	if ( ! is_user_logged_in() || ! current_user_can( 'read' ) ) {
		wp_send_json_error( array( 'message' => __( 'Permission denied', 'artpulse' ) ), 403 );
	}

	$layout = array();

	// Prefer POST parameter when the request is form encoded
	if ( isset( $_POST['layout'] ) ) {
		$layout_raw = $_POST['layout'];
		if ( is_string( $layout_raw ) ) {
			$layout = json_decode( stripslashes( $layout_raw ), true );
		}
	} else {
		// Fallback to JSON body when sent via fetch()
		$input = json_decode( file_get_contents( 'php://input' ), true );
		if ( is_array( $input ) && isset( $input['layout'] ) ) {
			$layout = $input['layout'];
		}
	}

	$user_id = get_current_user_id();

	if ( $user_id && is_array( $layout ) ) {
		DashboardWidgetManager::saveUserLayout( $user_id, $layout );
		wp_send_json_success( array( 'message' => 'Layout saved' ) );
	}

	wp_send_json_error( array( 'message' => 'Invalid data' ) );
}

function ap_save_widget_order(): void {
	check_admin_referer( 'ap_save_widget_order' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Cheatin&#8217; uh?' ) );
	}
	check_ajax_referer( 'ap_widget_order', 'nonce' );
	$order      = isset( $_POST['order'] ) ? json_decode( stripslashes( $_POST['order'] ), true ) : array();
	$identifier = isset( $_POST['identifier'] ) ? intval( $_POST['identifier'] ) : 0;
	$user_id    = $identifier > 0 ? $identifier : get_current_user_id();
	update_user_meta( $user_id, 'ap_widget_order', $order );
	wp_send_json_success();
}

function ap_save_dashboard_order_callback(): void {
	check_admin_referer( 'ap_save_dashboard_order_callback' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Cheatin&#8217; uh?' ) );
	}
	check_ajax_referer( 'ap_dashboard_nonce', 'nonce' );

	$order = json_decode( stripslashes( $_POST['order'] ?? '[]' ), true );
	if ( ! is_array( $order ) ) {
		wp_send_json_error( array( 'message' => 'Invalid format.' ) );
	}

	update_user_meta( get_current_user_id(), 'ap_dashboard_order', $order );

	wp_send_json_success( array( 'message' => 'Dashboard order saved.' ) );
}

function ap_load_dashboard_template( string $template, array $vars = array() ): string {
	$path = locate_template( $template );
	if ( ! $path ) {
		$path = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'templates/' . $template;
	}
	if ( ! file_exists( $path ) ) {
		return '';
	}
	ob_start();
	if ( $vars ) {
		extract( $vars, EXTR_SKIP );
	}
	include $path;
	return ob_get_clean();
}

function ap_widget_membership( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/membership.php', $vars );
}
register_ap_widget(
	'widget_membership',
	array(
		'title'    => 'Membership',
		'callback' => 'ap_widget_membership',
	)
);


function ap_widget_next_payment( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/next-payment.php', $vars );
}
register_ap_widget(
	'widget_next_payment',
	array(
		'title'    => 'Next Payment',
		'callback' => 'ap_widget_next_payment',
	)
);


function ap_widget_transactions( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/transactions.php', $vars );
}
register_ap_widget(
	'widget_transactions',
	array(
		'title'    => 'Transactions',
		'callback' => 'ap_widget_transactions',
	)
);


function ap_widget_upgrade( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/upgrade.php', $vars );
}
register_ap_widget(
	'widget_upgrade',
	array(
		'title'    => 'Upgrade',
		'callback' => 'ap_widget_upgrade',
	)
);


function ap_widget_content( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/content.php', $vars );
}
register_ap_widget(
	'widget_content',
	array(
		'title'    => 'Content',
		'callback' => 'ap_widget_content',
	)
);


function ap_widget_local_events( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/local-events.php', $vars );
}
register_ap_widget(
	'widget_local_events',
	array(
		'title'    => 'Local Events',
		'callback' => 'ap_widget_local_events',
	)
);


function ap_widget_favorites( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/favorites.php', $vars );
}
register_ap_widget(
	'widget_favorites',
	array(
		'title'    => 'Favorites',
		'callback' => 'ap_widget_favorites',
	)
);


function ap_widget_my_favorites( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/my-favorites.php', $vars );
}
register_ap_widget(
	'widget_my_favorites',
	array(
		'title'    => 'My Favorites',
		'callback' => 'ap_widget_my_favorites',
	)
);


function ap_widget_my_follows( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/my-follows.php', $vars );
}
register_ap_widget(
	'widget_my_follows',
	array(
		'title'    => 'My Follows',
		'callback' => 'ap_widget_my_follows',
	)
);


function ap_widget_creator_tips( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/widget-creator-tips.php', $vars );
}
register_ap_widget(
	'widget_creator_tips',
	array(
		'title'    => 'Creator Tips',
		'callback' => 'ap_widget_creator_tips',
	)
);


function ap_widget_rsvps( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/rsvps.php', $vars );
}
register_ap_widget(
	'widget_rsvps',
	array(
		'title'    => 'Rsvps',
		'callback' => 'ap_widget_rsvps',
	)
);


function ap_widget_rsvp_stats( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/rsvp-stats.php', $vars );
}
register_ap_widget(
	'widget_rsvp_stats',
	array(
		'title'    => 'Rsvp Stats',
		'callback' => 'ap_widget_rsvp_stats',
	)
);


function ap_widget_my_events( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/my-events.php', $vars );
}
register_ap_widget(
	'widget_my_events',
	array(
		'title'    => 'My Events',
		'callback' => 'ap_widget_my_events',
	)
);


function ap_widget_events( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/events.php', $vars );
}
register_ap_widget(
	'widget_events',
	array(
		'title'    => 'Events',
		'callback' => 'ap_widget_events',
	)
);


function ap_widget_support_history( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/support-history.php', $vars );
}
register_ap_widget(
	'widget_support_history',
	array(
		'title'    => 'Support History',
		'callback' => 'ap_widget_support_history',
	)
);


function ap_widget_notifications( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/notifications.php', $vars );
}
register_ap_widget(
	'widget_notifications',
	array(
		'title'    => 'Notifications',
		'callback' => 'ap_widget_notifications',
	)
);


function ap_widget_messages( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/messages.php', $vars );
}
register_ap_widget(
	'widget_messages',
	array(
		'title'    => 'Messages',
		'callback' => 'ap_widget_messages',
	)
);


function ap_widget_for_you( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/widget-for-you.php', $vars );
}
register_ap_widget(
	'widget_for_you',
	array(
		'title'    => 'For You',
		'callback' => 'ap_widget_for_you',
	)
);


function ap_widget_followed_artists( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/widget-followed-artists.php', $vars );
}
register_ap_widget(
	'widget_followed_artists',
	array(
		'title'    => 'Followed Artists',
		'callback' => 'ap_widget_followed_artists',
	)
);


function ap_widget_account_tools( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/account-tools.php', $vars );
}
register_ap_widget(
	'widget_account_tools',
	array(
		'title'    => 'Account Tools',
		'callback' => 'ap_widget_account_tools',
	)
);


function ap_widget_webhooks( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/webhooks.php', $vars );
}
register_ap_widget(
	'widget_webhooks',
	array(
		'title'    => 'Webhooks',
		'callback' => 'ap_widget_webhooks',
	)
);


function ap_widget_instagram( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/instagram-widget.php', $vars );
}
register_ap_widget(
	'widget_instagram',
	array(
		'title'    => 'Instagram',
		'callback' => 'ap_widget_instagram',
	)
);


function ap_widget_cat_fact( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/cat-fact.php', $vars );
}
register_ap_widget(
	'widget_cat_fact',
	array(
		'title'    => 'Cat Fact',
		'callback' => 'ap_widget_cat_fact',
	)
);


function ap_widget_spotlights( int $user_id = 0, array $vars = array() ): string {
	$vars['role'] = DashboardController::get_role( get_current_user_id() );
	return ap_load_dashboard_template( 'widgets/widget-spotlights.php', $vars );
}
register_ap_widget(
	'widget_spotlights',
	array(
		'title'    => 'Spotlights',
		'callback' => 'ap_widget_spotlights',
	)
);


function ap_widget_role_spotlight( int $user_id = 0, array $vars = array() ): string {
	$vars['role']      = DashboardController::get_role( get_current_user_id() );
	$vars['widget_id'] = 'role-spotlight';
	return ap_load_dashboard_template( 'widgets/spotlight-dashboard.php', $vars );
}
register_ap_widget(
	'widget_role_spotlight',
	array(
		'title'    => 'Role Spotlight',
		'callback' => 'ap_widget_role_spotlight',
	)
);


function ap_widget_spotlight_calls( int $user_id = 0, array $vars = array() ): string {
	$vars['role'] = DashboardController::get_role( get_current_user_id() );
	return ap_load_dashboard_template( 'widgets/spotlight-dashboard-calls.php', $vars );
}
register_ap_widget(
	'widget_spotlight_calls',
	array(
		'title'    => 'Spotlight Calls',
		'callback' => 'ap_widget_spotlight_calls',
	)
);


function ap_widget_spotlight_events( int $user_id = 0, array $vars = array() ): string {
	$vars['role'] = DashboardController::get_role( get_current_user_id() );
	return ap_load_dashboard_template( 'widgets/spotlight-dashboard-events.php', $vars );
}
register_ap_widget(
	'widget_spotlight_events',
	array(
		'title'    => 'Spotlight Events',
		'callback' => 'ap_widget_spotlight_events',
	)
);


function ap_widget_spotlight_features( int $user_id = 0, array $vars = array() ): string {
	$vars['role'] = DashboardController::get_role( get_current_user_id() );
	return ap_load_dashboard_template( 'widgets/spotlight-dashboard-features.php', $vars );
}
register_ap_widget(
	'widget_spotlight_features',
	array(
		'title'    => 'Spotlight Features',
		'callback' => 'ap_widget_spotlight_features',
	)
);


function ap_widget_upcoming_events_location( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/upcoming-events-location.php', $vars );
}
register_ap_widget(
	'widget_upcoming_events_location',
	array(
		'title'    => 'Upcoming Events Location',
		'callback' => 'ap_widget_upcoming_events_location',
	)
);


function ap_widget_followed_artists_activity( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/followed-artists-activity.php', $vars );
}
register_ap_widget(
	'widget_followed_artists_activity',
	array(
		'title'    => 'Followed Artists Activity',
		'callback' => 'ap_widget_followed_artists_activity',
	)
);


function ap_widget_artist_inbox_preview( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/artist-inbox-preview.php', $vars );
}
register_ap_widget(
	'widget_artist_inbox_preview',
	array(
		'title'    => 'Artist Inbox Preview',
		'callback' => 'ap_widget_artist_inbox_preview',
	)
);


function ap_widget_artist_revenue_summary( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/artist-revenue-summary.php', $vars );
}
register_ap_widget(
	'widget_artist_revenue_summary',
	array(
		'title'    => 'Artist Revenue Summary',
		'callback' => 'ap_widget_artist_revenue_summary',
	)
);


function ap_widget_artist_spotlight( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/artist-spotlight-widget.php', $vars );
}
register_ap_widget(
	'widget_artist_spotlight',
	array(
		'title'    => 'Artist Spotlight',
		'callback' => 'ap_widget_artist_spotlight',
	)
);


function ap_widget_artist_artwork_manager( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/artist-artwork-manager.php', $vars );
}
register_ap_widget(
	'widget_artist_artwork_manager',
	array(
		'title'    => 'Artist Artwork Manager',
		'callback' => 'ap_widget_artist_artwork_manager',
	)
);


function ap_widget_artist_audience_insights( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/artist-audience-insights.php', $vars );
}
register_ap_widget(
	'widget_artist_audience_insights',
	array(
		'title'    => 'Artist Audience Insights',
		'callback' => 'ap_widget_artist_audience_insights',
	)
);


function ap_widget_artist_earnings_summary( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/artist-earnings.php', $vars );
}
register_ap_widget(
	'widget_artist_earnings_summary',
	array(
		'title'    => 'Artist Earnings Summary',
		'callback' => 'ap_widget_artist_earnings_summary',
	)
);


function ap_widget_artist_feed_publisher( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/artist-feed-publisher.php', $vars );
}
register_ap_widget(
	'widget_artist_feed_publisher',
	array(
		'title'    => 'Artist Feed Publisher',
		'callback' => 'ap_widget_artist_feed_publisher',
	)
);


function ap_widget_collab_requests( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/collab-requests.php', $vars );
}
register_ap_widget(
	'widget_collab_requests',
	array(
		'title'    => 'Collab Requests',
		'callback' => 'ap_widget_collab_requests',
	)
);


function ap_widget_onboarding_tracker( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/onboarding-tracker.php', $vars );
}
register_ap_widget(
	'widget_onboarding_tracker',
	array(
		'title'    => 'Onboarding Tracker',
		'callback' => 'ap_widget_onboarding_tracker',
	)
);


function ap_widget_my_rsvps( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/my-rsvps.php', $vars );
}
register_ap_widget(
	'widget_my_rsvps',
	array(
		'title'    => 'My Rsvps',
		'callback' => 'ap_widget_my_rsvps',
	)
);


function ap_widget_my_shared_events_activity( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/my-shared-events-activity.php', $vars );
}
register_ap_widget(
	'widget_my_shared_events_activity',
	array(
		'title'    => 'My Shared Events Activity',
		'callback' => 'ap_widget_my_shared_events_activity',
	)
);


function ap_widget_recommended_for_you_member( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/recommended-for-you.php', $vars );
}
register_ap_widget(
	'widget_recommended_for_you_member',
	array(
		'title'    => 'Recommended For You Member',
		'callback' => 'ap_widget_recommended_for_you_member',
	)
);


function ap_widget_dashboard_feedback( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/dashboard-feedback.php', $vars );
}
register_ap_widget(
	'widget_dashboard_feedback',
	array(
		'title'    => 'Dashboard Feedback',
		'callback' => 'ap_widget_dashboard_feedback',
	)
);


function ap_widget_rsvp_button( int $user_id = 0, array $vars = array() ): string {
	ob_start();
	ap_render_js_widget( 'rsvp_button', array( 'eventId' => $vars['event_id'] ?? 0 ) );
	return ob_get_clean();
}
register_ap_widget(
	'widget_rsvp_button',
	array(
		'title'    => 'Rsvp Button',
		'callback' => 'ap_widget_rsvp_button',
	)
);


function ap_widget_event_chat( int $user_id = 0, array $vars = array() ): string {
	ob_start();
	ap_render_js_widget( 'event_chat', array( 'eventId' => $vars['event_id'] ?? 0 ) );
	return ob_get_clean();
}
register_ap_widget(
	'widget_event_chat',
	array(
		'title'    => 'Event Chat',
		'callback' => 'ap_widget_event_chat',
	)
);


function ap_widget_share_this_event( int $user_id = 0, array $vars = array() ): string {
	ob_start();
	ap_render_js_widget( 'share_this_event', array( 'eventUrl' => $vars['event_url'] ?? '' ) );
	return ob_get_clean();
}
register_ap_widget(
	'widget_share_this_event',
	array(
		'title'    => 'Share This Event',
		'callback' => 'ap_widget_share_this_event',
	)
);


function ap_widget_audience_crm( int $user_id = 0, array $vars = array() ): string {
	ob_start();
	ap_render_js_widget( 'audience_crm' );
	return ob_get_clean();
}
register_ap_widget(
	'widget_audience_crm',
	array(
		'title'    => 'Audience Crm',
		'callback' => 'ap_widget_audience_crm',
	)
);


function ap_widget_sponsored_event_config( int $user_id = 0, array $vars = array() ): string {
	ob_start();
	ap_render_js_widget( 'sponsored_event_config' );
	return ob_get_clean();
}
register_ap_widget(
	'widget_sponsored_event_config',
	array(
		'title'    => 'Sponsored Event Config',
		'callback' => 'ap_widget_sponsored_event_config',
	)
);


function ap_widget_embed_tool( int $user_id = 0, array $vars = array() ): string {
	ob_start();
	ap_render_js_widget( 'embed_tool' );
	return ob_get_clean();
}
register_ap_widget(
	'widget_embed_tool',
	array(
		'title'    => 'Embed Tool',
		'callback' => 'ap_widget_embed_tool',
	)
);


function ap_widget_org_event_overview( int $user_id = 0, array $vars = array() ): string {
	ob_start();
	ap_render_js_widget( 'org_event_overview' );
	return ob_get_clean();
}
register_ap_widget(
	'widget_org_event_overview',
	array(
		'title'    => 'Org Event Overview',
		'callback' => 'ap_widget_org_event_overview',
	)
);


function ap_widget_org_team_roster( int $user_id = 0, array $vars = array() ): string {
	ob_start();
	ap_render_js_widget( 'org_team_roster' );
	return ob_get_clean();
}
register_ap_widget(
	'widget_org_team_roster',
	array(
		'title'    => 'Org Team Roster',
		'callback' => 'ap_widget_org_team_roster',
	)
);


function ap_widget_branding_settings_panel( int $user_id = 0, array $vars = array() ): string {
	ob_start();
	ap_render_js_widget( 'branding_settings_panel' );
	return ob_get_clean();
}
register_ap_widget(
	'widget_branding_settings_panel',
	array(
		'title'    => 'Branding Settings Panel',
		'callback' => 'ap_widget_branding_settings_panel',
	)
);


function ap_widget_org_widget_sharing( int $user_id = 0, array $vars = array() ): string {
	ob_start();
	ap_render_js_widget( 'org_widget_sharing' );
	return ob_get_clean();
}
register_ap_widget(
	'widget_org_widget_sharing',
	array(
		'title'    => 'Org Widget Sharing',
		'callback' => 'ap_widget_org_widget_sharing',
	)
);


function ap_widget_sponsor_display( int $user_id = 0, array $vars = array() ): string {
	ob_start();
	ap_render_js_widget( 'sponsor_display' );
	return ob_get_clean();
}
register_ap_widget(
	'widget_sponsor_display',
	array(
		'title'    => 'Sponsor Display',
		'callback' => 'ap_widget_sponsor_display',
	)
);


function ap_widget_org_approval_center( int $user_id = 0, array $vars = array() ): string {
	ob_start();
	ap_render_js_widget( 'org_approval_center' );
	return ob_get_clean();
}
register_ap_widget(
	'widget_org_approval_center',
	array(
		'title'    => 'Org Approval Center',
		'callback' => 'ap_widget_org_approval_center',
	)
);


function ap_widget_org_ticket_insights( int $user_id = 0, array $vars = array() ): string {
	ob_start();
	ap_render_js_widget( 'org_ticket_insights' );
	return ob_get_clean();
}
register_ap_widget(
	'widget_org_ticket_insights',
	array(
		'title'    => 'Org Ticket Insights',
		'callback' => 'ap_widget_org_ticket_insights',
	)
);


function ap_widget_org_broadcast_box( int $user_id = 0, array $vars = array() ): string {
	ob_start();
	ap_render_js_widget( 'org_broadcast_box' );
	return ob_get_clean();
}
register_ap_widget(
	'widget_org_broadcast_box',
	array(
		'title'    => 'Org Broadcast Box',
		'callback' => 'ap_widget_org_broadcast_box',
	)
);


// Core widgets are registered automatically alongside their render callbacks.
