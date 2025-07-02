<?php
add_action('admin_init', function () {
    register_setting('ap_settings_group', 'ap_ui_mode');
});

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
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    });
});
