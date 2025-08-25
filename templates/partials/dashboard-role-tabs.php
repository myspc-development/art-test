<?php
if (function_exists('ap_dashboard_v2_enabled') && !ap_dashboard_v2_enabled()) {
    return;
}
$roles = array_intersect(['member','artist','organization'], wp_get_current_user()->roles);
if (count($roles) <= 1) {
    return; // no tabs needed
}
$labels = [
    'member'       => __('Member Tools', 'artpulse'),
    'artist'       => __('Artist Tools', 'artpulse'),
    'organization' => __('Org Tools', 'artpulse'),
];
$icons = [
    'member' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="7" r="4"/><path d="M5.5 21a10 10 0 0 1 13 0"/></svg>',
    'artist' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="7"/><circle cx="10" cy="10" r="1"/><circle cx="14" cy="10" r="1"/><path d="M8 16c1.333-1.333 4.667-1.333 6 0"/></svg>',
    'organization' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18"/><path d="M6 21V9h12v12"/><path d="M9 21V12h6v9"/></svg>',
];
wp_enqueue_script('ap-role-tabs', plugin_dir_url(__FILE__) . '../../assets/js/dashboard-role-tabs.js', [], null, true);
$active_role = reset($roles);
?>
<div class="ap-role-tabs" role="tablist" aria-label="<?php esc_attr_e('Dashboard role tabs', 'artpulse'); ?>">
  <?php foreach ($roles as $role): ?>
    <?php $is_active = ($role === $active_role); ?>
    <button type="button"
      class="ap-role-tab<?= $is_active ? ' active' : '' ?>"
      role="tab"
      id="ap-tab-<?php echo esc_attr($role); ?>"
      aria-controls="ap-panel-<?php echo esc_attr($role); ?>"
      aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
      tabindex="<?php echo $is_active ? '0' : '-1'; ?>"
      data-role="<?php echo esc_attr($role); ?>">
      <?php if (isset($icons[$role])): ?>
        <span class="ap-role-icon" aria-hidden="true"><?php echo $icons[$role]; ?></span>
      <?php endif; ?>
      <span class="ap-role-label"><?php echo esc_html($labels[$role] ?? ucfirst($role)); ?></span>
    </button>
  <?php endforeach; ?>
</div>
