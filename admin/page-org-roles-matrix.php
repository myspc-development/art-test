<?php
if (!defined('ABSPATH')) { exit; }

$users = get_users();
$roles = ['org_manager', 'org_editor', 'org_viewer'];
$role_names = wp_roles()->roles;

wp_enqueue_script('ap-role-matrix-js');
wp_localize_script('ap-role-matrix-js', 'AP_RoleMatrix', [
    'nonce'    => wp_create_nonce('ap_role_matrix_nonce'),
    'rest_url' => rest_url('artpulse/v1/roles/toggle')
]);
?>
<div class="wrap">
  <h1><?php esc_html_e('Organization Role Matrix', 'artpulse'); ?></h1>
  <table class="wp-list-table widefat fixed striped">
    <thead>
      <tr><th><?php esc_html_e('User', 'artpulse'); ?></th>
      <?php foreach ($roles as $role): ?>
        <th><?= esc_html($role_names[$role]['name'] ?? $role) ?></th>
      <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $user): ?>
      <tr data-user-id="<?= esc_attr($user->ID) ?>">
        <td><?= esc_html($user->display_name) ?></td>
        <?php foreach ($roles as $role): ?>
          <td><input type="checkbox" class="ap-role-toggle" data-role="<?= esc_attr($role) ?>" <?= in_array($role, $user->roles, true) ? 'checked' : '' ?> /></td>
        <?php endforeach; ?>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
