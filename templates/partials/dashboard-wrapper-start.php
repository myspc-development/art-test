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
      ap_safe_include('partials/dashboard-nav.php', plugin_dir_path(__FILE__) . 'dashboard-nav.php');
      ?>
    </aside>
    <main class="ap-dashboard-main">
      <?php
      $user    = wp_get_current_user();
      $followed = \ArtPulse\Community\FollowManager::get_user_follows($user->ID, 'artist');
      $follows = is_array($followed) ? count($followed) : 0;
      $summary = \ArtPulse\Frontend\EventRsvpHandler::get_rsvp_summary_for_user($user->ID);
      $events  = $summary['going'] ?? 0;
      ?>
      <h2>Welcome back, <?= esc_html($user->display_name) ?> 👋</h2>
      <p>You're following <?= $follows ?> artists. You’ve RSVP’d to <?= $events ?> events.</p>

