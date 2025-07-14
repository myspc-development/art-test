<?php
if (!defined('ABSPATH')) { exit; }

if (isset($_POST['ap_update_user_role']) && check_admin_referer('ap_change_role_' . $_POST['user_id'])) {
    $user = get_user_by('id', absint($_POST['user_id']));
    if ($user) {
        $user->set_role(sanitize_key($_POST['new_role']));
        ap_add_admin_notice("Updated role for {$user->display_name}", 'success');
    }
}

$users = get_users();
$roles = wp_roles()->roles;
?>
<div class="wrap">
  <h1><?php esc_html_e('Organization Roles', 'artpulse'); ?></h1>
  <table class="wp-list-table widefat">
    <thead><tr><th><?php esc_html_e('User', 'artpulse'); ?></th><th><?php esc_html_e('Role', 'artpulse'); ?></th><th><?php esc_html_e('Change', 'artpulse'); ?></th></tr></thead>
    <tbody>
      <?php foreach ($users as $user): ?>
      <tr>
        <td><?= esc_html($user->display_name) ?></td>
        <td><?= esc_html(implode(', ', $user->roles)) ?></td>
        <td>
          <form method="post">
            <?php wp_nonce_field('ap_change_role_' . $user->ID); ?>
            <select name="new_role">
              <?php foreach ($roles as $key => $r): ?>
                <option value="<?= esc_attr($key) ?>"><?= esc_html($r['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <input type="hidden" name="user_id" value="<?= esc_attr($user->ID) ?>">
            <input type="submit" name="ap_update_user_role" value="<?php esc_attr_e('Update', 'artpulse'); ?>" class="button">
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
