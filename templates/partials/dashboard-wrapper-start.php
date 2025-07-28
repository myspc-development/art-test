<?php
/**
 * Dashboard wrapper start.
 *
 * Expected variables: $dashboard_class (string), $dashboard_title (string).
 */
?>
<div class="ap-dashboard-wrap <?php echo esc_attr($dashboard_class ?? ''); ?>">
  <h2 class="ap-card__title"><?php echo esc_html($dashboard_title ?? __('Dashboard', 'artpulse')); ?></h2>
  <form method="post" class="ap-dashboard-reset ap-inline-form">
    <?php wp_nonce_field('ap_reset_user_layout'); ?>
    <input type="hidden" name="reset_user_layout" value="1" />
    <button class="button"><?php esc_html_e('♻ Reset My Dashboard', 'artpulse'); ?></button>
  </form>

  <?php $user = wp_get_current_user(); ?>
  <header class="dashboard-topbar">
    <div class="welcome">
      <img src="<?php echo esc_url(get_avatar_url($user->ID, ['size' => 60])); ?>" alt="" />
      <div>
        <h2 class="ap-card__title"><?php printf(__('Welcome back, %s', 'artpulse'), esc_html($user->display_name)); ?></h2>
        <span class="membership-status">
          <?php
          $level = \ArtPulse\ap_user_membership_level($user->ID);
          echo esc_html(sprintf(__('Membership: %s', 'artpulse'), ucfirst($level)));
          ?>
        </span>
      </div>
    </div>
  </header>

  <div class="ap-dashboard-layout">
    <aside class="ap-dashboard-sidebar">
      <?php
      $show_notifications = true; // Optional logic to toggle certain links
      ap_safe_include('partials/dashboard-nav.php', plugin_dir_path(__FILE__) . 'dashboard-nav.php');
      ?>
    </aside>
    <main class="ap-dashboard-main">
      <?php
      $followed = \ArtPulse\Community\FollowManager::get_user_follows($user->ID, 'artist');
      $follows  = is_array($followed) ? count($followed) : 0;
      $orgs     = \ArtPulse\Community\FollowManager::get_user_follows($user->ID, 'artpulse_org');
      $org_count = is_array($orgs) ? count($orgs) : 0;
      $summary = \ArtPulse\Frontend\EventRsvpHandler::get_rsvp_summary_for_user($user->ID);
      $events  = $summary['going'] ?? 0;
      ?>
      <p><?php printf(__('You\'re following %d artists and %d organizations. You’ve RSVP’d to %d events.', 'artpulse'), $follows, $org_count, $events); ?></p>
      <?php if ($org_count === 0): ?>
        <p><?php esc_html_e('Follow organizations to see more events in your digest.', 'artpulse'); ?></p>
      <?php endif; ?>

