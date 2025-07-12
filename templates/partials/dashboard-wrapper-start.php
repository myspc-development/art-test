<?php
/**
 * Dashboard wrapper start.
 *
 * Expected variables: $dashboard_class (string), $dashboard_title (string).
 */
?>
<div class="ap-dashboard-wrap <?php echo esc_attr($dashboard_class ?? ''); ?>">
  <h2><?php echo esc_html($dashboard_title ?? __('Dashboard', 'artpulse')); ?></h2>
  <form method="post" class="ap-dashboard-reset" style="margin-bottom:1em;">
    <?php wp_nonce_field('ap_reset_user_layout'); ?>
    <input type="hidden" name="reset_user_layout" value="1" />
    <button class="button"><?php esc_html_e('♻ Reset My Dashboard', 'artpulse'); ?></button>
  </form>

  <div class="ap-dashboard-layout">
    <aside class="ap-dashboard-sidebar">
      <?php
      $show_notifications = true; // Optional logic to toggle certain links
      include locate_template('partials/dashboard-nav.php');
      ?>
    </aside>
    <main class="ap-dashboard-main">

