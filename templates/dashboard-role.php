<?php
if (!defined('AP_DASHBOARD_RENDERING')) {
    $role = isset($_GET['ap_preview_role']) ? sanitize_key($_GET['ap_preview_role']) : null;
    ap_render_dashboard($role ? [$role] : []);
    return;
}

?>
<?php
static $first_panel = true;
$is_active = $first_panel;
$first_panel = false;
?>
<section class="ap-role-layout" role="tabpanel" id="ap-panel-<?php echo esc_attr($user_role ?? ''); ?>" aria-labelledby="ap-tab-<?php echo esc_attr($user_role ?? ''); ?>" data-role="<?php echo esc_attr($user_role ?? ''); ?>"<?php echo $is_active ? '' : ' hidden'; ?>>
  <div id="ap-dashboard-root" class="ap-dashboard-grid" role="grid" aria-label="<?php esc_attr_e('Dashboard widgets', 'artpulse'); ?>" data-role="<?php echo esc_attr($user_role ?? ''); ?>"></div>
</section>
