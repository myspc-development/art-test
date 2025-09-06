<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Core\DashboardWidgetRegistry;

add_action(
	'admin_menu',
	function () {
		add_submenu_page(
			'artpulse-settings',
			__( 'Widget Health', 'artpulse' ),
			__( 'Widget Health', 'artpulse' ),
			'manage_options',
			'widget-health',
			'ap_render_widget_health_page'
		);
	}
);

function ap_render_widget_health_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Insufficient permissions', 'artpulse' ) );
	}

	$roles = array( 'member', 'artist', 'organization' );

	echo '<div class="wrap">';
	echo '<h1>' . esc_html__( 'Widget Health', 'artpulse' ) . '</h1>';
	echo '<p><a href="' . esc_url( admin_url( 'admin.php?page=widget-health' ) ) . '" class="button">' . esc_html__( 'Refresh', 'artpulse' ) . '</a></p>';
	echo '<style>.ap-widget-error{background:#fdd;}</style>';

	foreach ( $roles as $role ) {
		$layout_result      = UserLayoutManager::get_role_layout( $role );
		$layout             = $layout_result['layout'] ?? array();
		$registered_widgets = DashboardWidgetRegistry::get_widgets( $role );

		echo '<h2>' . esc_html( ucfirst( $role ) ) . '</h2>';
		echo '<table class="widefat fixed striped">';
		echo '<thead><tr>';
		echo '<th>' . esc_html__( 'Widget ID', 'artpulse' ) . '</th>';
		echo '<th>' . esc_html__( 'Registered', 'artpulse' ) . '</th>';
		echo '<th>' . esc_html__( 'Callable', 'artpulse' ) . '</th>';
		echo '<th>' . esc_html__( 'Visible', 'artpulse' ) . '</th>';
		echo '<th>' . esc_html__( 'Status', 'artpulse' ) . '</th>';
		echo '</tr></thead><tbody>';

		foreach ( $layout as $item ) {
			$id = $item['id'] ?? '';
			if ( ! $id ) {
				continue;
			}

			$registered = isset( $registered_widgets[ $id ] );
			$callable   = false;
			$visible    = false;

			if ( $registered ) {
				$def = DashboardWidgetRegistry::getById( $id );
				if ( $def ) {
					$callback = $def['callback'] ?? null;
					if ( is_callable( $callback ) ) {
						$callable = true;
					} elseif ( ! empty( $def['class'] ) && is_callable( array( $def['class'], 'render' ) ) ) {
						$callable = true;
					}
				}
				$prev                    = $_GET['ap_preview_role'] ?? null;
				$_GET['ap_preview_role'] = $role;
				$visible                 = DashboardWidgetRegistry::user_can_see( $id );
				if ( $prev !== null ) {
					$_GET['ap_preview_role'] = $prev;
				} else {
					unset( $_GET['ap_preview_role'] );
				}
			}

			if ( ! $registered ) {
				$status = __( 'Missing', 'artpulse' );
			} elseif ( ! $callable ) {
				$status = __( 'Broken', 'artpulse' );
			} elseif ( ! $visible ) {
				$status = __( 'Hidden', 'artpulse' );
			} else {
				$status = __( 'OK', 'artpulse' );
			}

			$row_class = ( $status !== __( 'OK', 'artpulse' ) ) ? ' class="ap-widget-error"' : '';

			echo '<tr' . $row_class . '>';
			echo '<td>' . esc_html( $id ) . '</td>';
			echo '<td>' . ( $registered ? '✅' : '❌' ) . '</td>';
			echo '<td>' . ( $callable ? '✅' : '❌' ) . '</td>';
			echo '<td>' . ( $visible ? '✅' : '❌' ) . '</td>';
			echo '<td>' . esc_html( $status ) . '</td>';
			echo '</tr>';
		}

		echo '</tbody></table>';
	}

	echo '</div>';
}
