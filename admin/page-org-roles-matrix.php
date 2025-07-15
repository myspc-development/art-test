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
    echo '<div id="ap-org-roles-root"></div>';
}
?>
</div>
