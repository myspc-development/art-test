<?php
$roles = array_intersect(['member','artist','organization'], wp_get_current_user()->roles);
if (count($roles) <= 1) {
    return; // no tabs needed
}
$labels = [
    'member' => __('Member Tools', 'artpulse'),
    'artist' => __('Artist Tools', 'artpulse'),
    'organization' => __('Org Tools', 'artpulse'),
];
wp_enqueue_script('ap-role-tabs', plugin_dir_url(__FILE__) . '../../assets/js/dashboard-role-tabs.js', [], null, true);
$active_role = reset($roles);
?>
<div class="ap-role-tabs" role="tablist">
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
      <?php echo esc_html($labels[$role] ?? ucfirst($role)); ?>
    </button>
  <?php endforeach; ?>
</div>
