<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\DashboardRenderer;

// Register the Dashboard Widgets Inspector admin page.
add_action(
	'admin_menu',
	function () {
		add_management_page(
			__( 'Dashboard Widgets Inspector', 'artpulse' ),
			__( 'Dashboard Widgets Inspector', 'artpulse' ),
			'manage_options',
			'ap-dashboard-widgets-inspector',
			'ap_render_dashboard_widgets_inspector'
		);
	}
);

/**
 * Render the Dashboard Widgets Inspector page.
 */
function ap_render_dashboard_widgets_inspector(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Insufficient permissions', 'artpulse' ) );
	}

	$format  = sanitize_text_field( $_GET['format'] ?? '' );
	$widgets = DashboardWidgetRegistry::get_all();

	// Export handlers.
	if ( $format === 'json' ) {
		wp_send_json( $widgets );
	} elseif ( $format === 'csv' ) {
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="dashboard-widgets.csv"' );
		$out = fopen( 'php://output', 'w' );
		fputcsv( $out, array( 'id', 'label', 'class', 'roles', 'cache', 'lazy' ) );
		foreach ( $widgets as $id => $def ) {
			$roles = isset( $def['roles'] ) ? implode( '|', (array) $def['roles'] ) : '';
			fputcsv(
				$out,
				array(
					$id,
					$def['label'] ?? '',
					$def['class'] ?? '',
					$roles,
					empty( $def['cache'] ) ? '0' : '1',
					empty( $def['lazy'] ) ? '0' : '1',
				)
			);
		}
		fclose( $out );
		exit;
	}

	echo '<div class="wrap">';
	echo '<h1>' . esc_html__( 'Dashboard Widgets Inspector', 'artpulse' ) . '</h1>';
	echo '<p><input type="search" id="ap-widget-search" placeholder="' . esc_attr__( 'Search widgets...', 'artpulse' ) . '"></p>';
	echo '<p><a class="button" href="' . esc_url( add_query_arg( 'format', 'json' ) ) . '">' . esc_html__( 'Export JSON', 'artpulse' ) . '</a> ';
	echo '<a class="button" href="' . esc_url( add_query_arg( 'format', 'csv' ) ) . '">' . esc_html__( 'Export CSV', 'artpulse' ) . '</a></p>';

	echo '<table class="widefat fixed striped" id="ap-widgets-table">';
	echo '<thead><tr>';
	echo '<th>' . esc_html__( 'ID', 'artpulse' ) . '</th>';
	echo '<th>' . esc_html__( 'Label', 'artpulse' ) . '</th>';
	echo '<th>' . esc_html__( 'Class', 'artpulse' ) . '</th>';
	echo '<th>' . esc_html__( 'Roles', 'artpulse' ) . '</th>';
	echo '<th>' . esc_html__( 'Caching', 'artpulse' ) . '</th>';
	echo '<th>' . esc_html__( 'Lazy', 'artpulse' ) . '</th>';
	echo '<th>' . esc_html__( 'Render Time (ms)', 'artpulse' ) . '</th>';
	echo '<th>' . esc_html__( 'Preview', 'artpulse' ) . '</th>';
	echo '</tr></thead><tbody>';

	foreach ( $widgets as $id => $def ) {
		$roles = $def['roles'] ?? array();
		$class = $def['class'] ?? '';
		if ( $class && class_exists( $class ) && is_callable( array( $class, 'roles' ) ) ) {
			$roles = (array) $class::roles();
		}
		$roles_list = $roles ? implode( ', ', $roles ) : '';

		$start   = microtime( true );
		$preview = DashboardRenderer::render( $id, get_current_user_id() );
		$time_ms = round( ( microtime( true ) - $start ) * 1000, 2 );
		if ( $time_ms > 500 && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '🐌 Slow dashboard widget ' . $id . ' took ' . $time_ms . 'ms' );
		}

		echo '<tr>';
		echo '<td>' . esc_html( $id ) . '</td>';
		echo '<td>' . esc_html( $def['label'] ?? '' ) . '</td>';
		echo '<td>' . esc_html( $class ) . '</td>';
		echo '<td>' . esc_html( $roles_list ) . '</td>';
		echo '<td>' . ( $def['cache'] ? esc_html__( 'Yes', 'artpulse' ) : esc_html__( 'No', 'artpulse' ) ) . '</td>';
		echo '<td>' . ( $def['lazy'] ? esc_html__( 'Yes', 'artpulse' ) : esc_html__( 'No', 'artpulse' ) ) . '</td>';
		echo '<td>' . esc_html( $time_ms ) . '</td>';
		echo '<td><div class="ap-widget-preview">' . wp_kses_post( $preview ) . '</div></td>';
		echo '</tr>';
	}

	echo '</tbody></table>';
	echo '</div>';
	?>
	<script>
	(function(){
		const searchInput = document.getElementById('ap-widget-search');
		const rows = document.querySelectorAll('#ap-widgets-table tbody tr');
		searchInput.addEventListener('keyup', function(){
			const term = this.value.toLowerCase();
			rows.forEach(row => {
				row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
			});
		});
	})();
	</script>
	<?php
}
