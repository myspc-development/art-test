<?php
if (!defined('ABSPATH')) { exit; }

wp_enqueue_script('ap-role-matrix-bundle');
wp_localize_script('ap-role-matrix-bundle', 'AP_RoleMatrix', [
    'nonce'      => wp_create_nonce('wp_rest'),
    'rest_seed'  => rest_url('artpulse/v1/roles/seed'),
    'rest_batch' => rest_url('artpulse/v1/roles/batch'),
]);
?>
<div class="wrap">
  <h1><?php esc_html_e('Organization Role Matrix', 'artpulse'); ?></h1>
  <div id="ap-role-matrix-root"></div>
</div>

<?php
add_action('admin_footer', function () {
    if (get_current_screen()->id === 'toplevel_page_ap-org-roles-matrix') {
        echo "<script>const { createElement, render } = wp.element; render(createElement(APRoleMatrix), document.getElementById('ap-role-matrix-root'));</script>";
    }
});
