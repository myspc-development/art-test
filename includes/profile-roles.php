<?php
if (!defined('ABSPATH')) { exit; }

add_action('show_user_profile', 'ap_show_extra_roles');
add_action('edit_user_profile', 'ap_show_extra_roles');

function ap_show_extra_roles($user) {
    if (!current_user_can('manage_options')) return;
    $roles = wp_roles()->roles;
    ?>
    <h2><?php esc_html_e('Organization Role', 'artpulse'); ?></h2>
    <select name="role">
        <?php foreach ($roles as $key => $r): ?>
            <option value="<?= esc_attr($key) ?>" <?= in_array($key, $user->roles) ? 'selected' : '' ?>>
                <?= esc_html($r['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php
}

add_action('personal_options_update', 'ap_save_profile_role');
add_action('edit_user_profile_update', 'ap_save_profile_role');

function ap_save_profile_role($user_id) {
    if (!current_user_can('edit_users')) return;
    $user = get_user_by('id', $user_id);
    $user->set_role(sanitize_key($_POST['role']));
}
