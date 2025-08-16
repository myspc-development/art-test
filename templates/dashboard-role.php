<?php
if (!defined('AP_DASHBOARD_RENDERING')) {
    $role = isset($_GET['ap_preview_role']) ? sanitize_key($_GET['ap_preview_role']) : null;
    ap_render_dashboard($role ? [$role] : []);
    return;
}

?>
<div id="ap-dashboard-root" class="ap-dashboard-grid" role="grid" aria-label="Dashboard widgets" data-role="<?php echo esc_attr($user_role ?? ''); ?>"></div>
