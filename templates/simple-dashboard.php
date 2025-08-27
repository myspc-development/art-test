<?php
/**
 * Template Name: Simple Dashboard
 *
 * Minimal template rendering widgets for the current user's role.
 */

if (!is_user_logged_in()) {
    return;
}

$role = get_query_var('ap_role') ?: 'member';
if (!in_array($role, ['member','artist','organization'], true)) {
    $role = 'member';
}
?>
<section
  class="ap-role-layout"
  role="tabpanel"
  id="ap-panel-<?php echo esc_attr($role); ?>"
  aria-labelledby="ap-tab-<?php echo esc_attr($role); ?>"
  data-role="<?php echo esc_attr($role); ?>"
>
  <?php foreach (\ArtPulse\Core\DashboardPresets::forRole($role) as $slug): ?>
      <?php echo \ArtPulse\Core\DashboardWidgetRegistry::render($slug); ?>
  <?php endforeach; ?>
</section>

