<?php
if (!defined('ABSPATH')) { exit; }

$active_tab = isset($_GET['view']) && $_GET['view'] === 'roles' ? 'roles' : 'matrix';
?>
<div class="wrap">
  <h1><?php esc_html_e('Organization Roles', 'artpulse'); ?></h1>
  <h2 class="nav-tab-wrapper">
    <a href="?page=ap-org-roles-matrix" class="nav-tab <?= $active_tab === 'matrix' ? 'nav-tab-active' : '' ?>">Matrix</a>
    <a href="?page=ap-org-roles-matrix&view=roles" class="nav-tab <?= $active_tab === 'roles' ? 'nav-tab-active' : '' ?>">Roles</a>
  </h2>

<?php
if ($active_tab === 'roles') {
    require_once plugin_dir_path(__FILE__) . '/page-org-roles.php';
} else {
    wp_enqueue_script('ap-role-matrix-bundle');
    wp_localize_script('ap-role-matrix-bundle', 'AP_RoleMatrix', [
        'nonce'      => wp_create_nonce('wp_rest'),
        'rest_seed'  => rest_url('artpulse/v1/roles/seed'),
        'rest_batch' => rest_url('artpulse/v1/roles/batch'),
    ]);
    echo '<div id="ap-role-matrix-root"></div>';
}
?>
</div>

<?php
add_action('admin_footer', function () use ($active_tab) {
    if ($active_tab === 'matrix' && get_current_screen()->id === 'toplevel_page_ap-org-roles-matrix') {
        echo "<script>const { createElement, render } = wp.element; render(createElement(APRoleMatrix), document.getElementById('ap-role-matrix-root'));</script>";
    }
});
