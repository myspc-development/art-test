<?php
add_action('admin_init', function () {
    register_setting('ap_settings_group', 'ap_ui_mode', [
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    register_setting('ap_settings_group', 'ap_portfolio_display', [
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
});

// === Rejection Reason Meta Box ===
add_action('add_meta_boxes', function () {
    add_meta_box(
        'ap_rejection_reason',
        __('Rejection Reason', 'artpulse'),
        'ap_render_rejection_reason_meta_box',
        'artpulse_event'
    );
});

function ap_render_rejection_reason_meta_box(WP_Post $post)
{
    $value = get_post_meta($post->ID, 'ap_rejection_reason', true);
    echo '<textarea rows="4" name="ap_rejection_reason">' . esc_textarea($value) . '</textarea>';
}

add_action('save_post_artpulse_event', function ($post_id, WP_Post $post) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    if (array_key_exists('ap_rejection_reason', $_POST)) {
        $reason = sanitize_textarea_field($_POST['ap_rejection_reason']);
        if ($reason === '') {
            delete_post_meta($post_id, 'ap_rejection_reason');
        } else {
            update_post_meta($post_id, 'ap_rejection_reason', $reason);
        }
    }
}, 10, 2);

add_action('admin_menu', function () {
    add_options_page('Art UI Settings', 'Art UI Settings', 'manage_options', 'art-ui-settings', function () {
        ?>
        <div class="wrap">
            <h1>UI Mode</h1>
            <form method="post" action="options.php">
                <?php settings_fields('ap_settings_group'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">UI Mode</th>
                        <td>
                            <select name="ap_ui_mode">
                                <option value="salient" <?php selected(get_option('ap_ui_mode'), 'salient'); ?>>Salient / WordPress Bakery</option>
                                <option value="react" <?php selected(get_option('ap_ui_mode'), 'react'); ?>>React</option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Portfolio Display</th>
                        <td>
                            <select name="ap_portfolio_display">
                                <option value="plugin" <?php selected(get_option('ap_portfolio_display', 'plugin'), 'plugin'); ?>>Plugin Templates</option>
                                <option value="salient" <?php selected(get_option('ap_portfolio_display', 'plugin'), 'salient'); ?>>Salient Templates</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    });
});
