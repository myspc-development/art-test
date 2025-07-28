<?php
use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;

$user_role = DashboardController::get_role( get_current_user_id() );
if ( $user_role !== 'artist' ) {
    wp_die( __( 'Access denied', 'artpulse' ) );
}

?>
<div class="ap-dashboard ap-dashboard--role-<?php echo esc_attr( $user_role ); ?>">
  <?php
  if ( class_exists( '\\ArtPulse\\Core\\DashboardWidgetRegistry' ) ) {
      $widgets = DashboardWidgetRegistry::get_widgets( $user_role );
      if ( empty( $widgets ) ) {
          echo '<p>' . esc_html__( 'No widgets available for your role.', 'artpulse' ) . '</p>';
      } else {
          DashboardWidgetRegistry::render_for_role( get_current_user_id() );
      }
  } else {
      echo '<p>' . esc_html__( 'Unable to load dashboard widgets. Please contact support.', 'artpulse' ) . '</p>';
  }
  ?>
</div>

