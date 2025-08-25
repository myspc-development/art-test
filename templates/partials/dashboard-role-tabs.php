<?php
if (function_exists('ap_dashboard_v2_enabled') && !ap_dashboard_v2_enabled()) {
    return;
}

$user  = wp_get_current_user();
$roles = array_values(array_intersect(['member','artist','organization'], $user->roles));
if (count($roles) <= 1) {
    return; // no tabs needed
}

$labels = [
    'member'       => __('Member Tools', 'artpulse'),
    'artist'       => __('Artist Tools', 'artpulse'),
    'organization' => __('Org Tools', 'artpulse'),
];

$icons = [
    'member' => '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><circle cx="12" cy="7" r="4" fill="currentColor"/><path d="M4 21c2-3 5-5 8-5s6 2 8 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
    'artist' => '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M3 21l7-7 2 2 7-7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="19" cy="5" r="2" fill="currentColor"/></svg>',
    'organization' => '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M3 21V9l9-6 9 6v12H3z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M9 21v-6h6v6" fill="none" stroke="currentColor" stroke-width="2"/></svg>',
];

// Pick active from ?role= if valid; otherwise first available role
$requested = isset($_GET['role']) ? sanitize_key($_GET['role']) : null;
$active    = ($requested && in_array($requested, $roles, true)) ? $requested : $roles[0];
?>
<div class="ap-role-tabs" role="tablist" aria-label="<?php echo esc_attr__('Switch role view', 'artpulse'); ?>">
  <?php foreach ($roles as $role): ?>
    <?php $is_active = ($role === $active); ?>
    <button
      type="button"
      class="ap-role-tab<?php echo $is_active ? ' active' : ''; ?>"
      role="tab"
      id="ap-tab-<?php echo esc_attr($role); ?>"
      aria-controls="ap-panel-<?php echo esc_attr($role); ?>"
      aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
      tabindex="<?php echo $is_active ? '0' : '-1'; ?>"
      data-role="<?php echo esc_attr($role); ?>">
      <?php if (isset($icons[$role])): ?>
        <span class="ap-role-icon" aria-hidden="true"><?php echo $icons[$role]; // safe: constant string ?></span>
      <?php endif; ?>
      <span class="ap-role-label"><?php echo esc_html($labels[$role] ?? ucfirst($role)); ?></span>
    </button>
  <?php endforeach; ?>
</div>
