<?php
use ArtPulse\Admin\DashboardWidgetTools;

if (!isset($user_role) || !ap_user_can_edit_layout($user_role)) {
    wp_die(__('Access denied', 'artpulse'));
}

get_header();
?>

<div class="ap-dashboard-wrap <?= esc_attr($user_role) ?>-dashboard">
  <h2><?= esc_html(ucfirst($user_role)) ?> Dashboard</h2>

  <?php if (isset($_GET['layout_reset'])): ?>
    <div class="notice notice-success is-dismissible">
      <p><?= __('Dashboard layout reset!', 'artpulse'); ?></p>
    </div>
  <?php endif; ?>
  <?php if (isset($_GET['preset_loaded'])): ?>
    <div class="notice notice-success is-dismissible">
      <p><?= __('Preset layout loaded.', 'artpulse'); ?></p>
    </div>
  <?php endif; ?>

  <form method="post" style="margin-bottom:1em;">
    <?php wp_nonce_field('ap_reset_user_layout'); ?>
    <input type="hidden" name="reset_user_layout" value="1" />
    <button class="button">♻ <?= __('Reset My Dashboard', 'artpulse'); ?></button>
  </form>

  <form method="post" style="margin-bottom:1em;">
    <?php wp_nonce_field('ap_reset_user_layout'); ?>
    <input type="hidden" name="preset_role" value="<?= esc_attr($user_role) ?>" />
    <select name="load_preset">
      <option value=""><?= __('Load Preset Layout...', 'artpulse'); ?></option>
      <option value="default"><?= __('Default', 'artpulse'); ?></option>
    </select>
    <button class="button">⤵ <?= __('Apply Preset', 'artpulse'); ?></button>
  </form>

  <label style="display:block;margin-bottom:1em;">
    <input type="checkbox" id="ap-toggle-dark-mode" />
    <?= __('Dark Mode', 'artpulse'); ?>
  </label>

  <div id="ap-user-dashboard" class="ap-dashboard-columns" role="region" tabindex="0" aria-label="User Dashboard Widgets">
    <?php DashboardWidgetTools::render_user_dashboard(get_current_user_id()); ?>
  </div>
</div>

<?php get_footer(); ?>
