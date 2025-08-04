<?php
if (!defined('AP_DASHBOARD_RENDERING')) {
    $role = isset($_GET['ap_preview_role']) ? sanitize_key($_GET['ap_preview_role']) : null;
    ap_render_dashboard($role ? [$role] : []);
    return;
}

use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Frontend\DashboardCard;
use ArtPulse\Admin\UserLayoutManager;

$allowed_roles = $allowed_roles ?? [];
$user_role     = $user_role ?? DashboardController::get_role(get_current_user_id());

if ( $allowed_roles && ! in_array( $user_role, $allowed_roles, true ) ) {
    wp_die( __( 'Access denied', 'artpulse' ) );
}

get_header();
?>
<main id="dashboard-main" role="main" tabindex="-1">
  <div class="dashboard-widgets-wrap">
    <div class="ap-dashboard ap-dashboard--role-<?php echo esc_attr( $user_role ); ?>">
      <?php
      if ( class_exists( '\\ArtPulse\\Core\\DashboardWidgetRegistry' ) ) {
          $user_id = get_current_user_id();
          $defs    = DashboardWidgetRegistry::get_widgets_by_role( $user_role, $user_id );
          if ( empty( $defs ) ) {
              echo '<p>' . esc_html__( 'No widgets available for your role.', 'artpulse' ) . '</p>';
          } else {
              $layout   = UserLayoutManager::get_role_layout( $user_role );
              $sections = [];
              $order    = [];
              foreach ( $layout['layout'] as $row ) {
                  $id      = sanitize_key( $row['id'] ?? '' );
                  $visible = isset( $row['visible'] ) ? (bool) $row['visible'] : true;
                  if ( ! $visible || ! isset( $defs[ $id ] ) ) {
                      continue;
                  }
                  $section = sanitize_key( $defs[ $id ]['section'] ?? '' );
                  if ( ! isset( $sections[ $section ] ) ) {
                      $sections[ $section ] = [];
                      $order[]              = $section;
                  }
                  $sections[ $section ][] = $id;
              }
              foreach ( $order as $sec ) {
                  echo '<section class="ap-widget-section">';
                  if ( $sec ) {
                      echo '<h2>' . esc_html( ucfirst( $sec ) ) . '</h2>';
                  }
                  foreach ( $sections[ $sec ] as $id ) {
                      echo DashboardCard::render( $id, $user_id );
                  }
                  echo '</section>';
              }
          }
      } else {
          echo '<p>' . esc_html__( 'Unable to load dashboard widgets. Please contact support.', 'artpulse' ) . '</p>';
      }
      ?>
    </div>
  </div>
</main>
<?php
get_footer();
?>

