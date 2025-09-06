<?php
// Dashboard role panel template
if ( ! defined( 'AP_DASHBOARD_RENDERING' ) ) {
	$role = isset( $_GET['ap_preview_role'] ) ? sanitize_key( $_GET['ap_preview_role'] ) : null;
	ap_render_dashboard( $role ? array( $role ) : array() );
	return;
}

static $first_panel = true;
$dashboard_v2       = function_exists( 'ap_dashboard_v2_enabled' ) ? ap_dashboard_v2_enabled() : true;

if ( $first_panel && $dashboard_v2 ) {
	ap_safe_include( 'partials/dashboard-role-tabs.php', plugin_dir_path( __FILE__ ) . 'partials/dashboard-role-tabs.php' );
}

$is_active   = $first_panel;
$first_panel = false;

// $user_role is provided by the caller (renderer) for each panel
?>
<section
	class="ap-role-layout"
	role="tabpanel"
	id="ap-panel-<?php echo esc_attr( $user_role ?? '' ); ?>"
	aria-labelledby="ap-tab-<?php echo esc_attr( $user_role ?? '' ); ?>"
	data-role="<?php echo esc_attr( $user_role ?? '' ); ?>"
	<?php echo $is_active ? '' : 'hidden'; ?>>

	<?php
	// Define sections (override/extend if needed)
	$sections = array(
		'overview' => array(
			'label'   => __( 'Overview', 'artpulse' ),
			'content' => function () use ( $dashboard_v2 ) {
				echo '<div id="ap-dashboard-root" class="ap-dashboard-grid" data-ap-v2="' . ( $dashboard_v2 ? '1' : '0' ) . '"></div>';
			},
		),
		'activity' => array(
			'label'   => __( 'Activity', 'artpulse' ),
			'content' => function () {
				echo '<div class="ap-dashboard-section-inner">' . esc_html__( 'Recent activity will appear here.', 'artpulse' ) . '</div>';
			},
		),
		'payments' => array(
			'label'   => __( 'Payments', 'artpulse' ),
			'content' => function () {
				echo '<div class="ap-dashboard-section-inner">' . esc_html__( 'Payment summaries will appear here.', 'artpulse' ) . '</div>';
			},
		),
	);

	// Sticky local nav (server-rendered for no-JS fallback)
	if ( $dashboard_v2 && $sections ) :
		$qs_role = isset( $_GET['role'] ) ? sanitize_key( $_GET['role'] ) : ( $user_role ?? '' );
		?>
	<nav class="ap-local-nav" aria-label="<?php echo esc_attr__( 'Dashboard sections', 'artpulse' ); ?>">
		<ul>
		<?php
		foreach ( $sections as $id => $info ) :
			$href = ( $qs_role ? '?role=' . urlencode( $qs_role ) : '' ) . '#' . $id;
			?>
			<li><a href="<?php echo esc_url( $href ); ?>"><?php echo esc_html( $info['label'] ); ?></a></li>
		<?php endforeach; ?>
		</ul>
	</nav>
	<?php endif; ?>

	<?php
	// Render sections
	foreach ( $sections as $id => $info ) {
		echo '<section id="' . esc_attr( $id ) . '" class="ap-dashboard-section" tabindex="-1">';
		if ( isset( $info['template'] ) ) {
			ap_safe_include( 'partials/' . basename( $info['template'] ), $info['template'] );
		} else {
			$info['content']();
		}
		echo '</section>';
	}
	?>
</section>
