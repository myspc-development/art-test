<?php
namespace ArtPulse\Admin;

class LoginEventsPage
{
    public static function register()
    {
        add_action('admin_menu', [self::class, 'addMenu']);
    }

    public static function addMenu()
    {
        add_submenu_page(
            'artpulse-settings',
            __('Login Events', 'artpulse'),
            __('Login Events', 'artpulse'),
            'manage_options',
            'ap-login-events',
            [self::class, 'render']
        );
    }

    public static function render()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_login_events';
        $events = $wpdb->get_results("SELECT * FROM $table ORDER BY login_at DESC LIMIT 100");

        if (isset($_GET['ap_export_csv'])) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="ap-login-events.csv"');
            $output = fopen('php://output', 'w');
            fputcsv($output, ['User', 'Login', 'Logout', 'IP']);
            foreach ($events as $event) {
                $user = get_user_by('ID', $event->user_id);
                fputcsv($output, [
                    $user ? $user->user_login : $event->user_id,
                    $event->login_at,
                    $event->logout_at,
                    $event->ip_address,
                ]);
            }
            fclose($output);
            exit;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Recent Login Events', 'artpulse'); ?></h1>
            <a href="<?php echo esc_url(add_query_arg('ap_export_csv', 1)); ?>" class="button button-secondary" style="margin-bottom:10px;">Export CSV</a>
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('User', 'artpulse'); ?></th>
                        <th><?php esc_html_e('Login', 'artpulse'); ?></th>
                        <th><?php esc_html_e('Logout', 'artpulse'); ?></th>
                        <th><?php esc_html_e('IP Address', 'artpulse'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td><a href="<?php echo esc_url(get_edit_user_link($event->user_id)); ?>"><?php echo esc_html(get_user_by('ID', $event->user_id)->user_login); ?></a></td>
                        <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($event->login_at))); ?></td>
                        <td><?php echo esc_html($event->logout_at ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($event->logout_at)) : '—'); ?></td>
                        <td><?php echo esc_html($event->ip_address); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($events)): ?>
                    <tr><td colspan="4"><?php esc_html_e('No login events found.', 'artpulse'); ?></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public static function install_login_events_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_login_events';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            login_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            logout_at DATETIME NULL DEFAULT NULL,
            KEY user_id (user_id),
            KEY login_at (login_at)
        ) $charset_collate;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function add_event($user_id, $ip)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_login_events';
        $wpdb->insert($table, [
            'user_id'   => $user_id,
            'ip_address'=> $ip,
            'login_at'  => current_time('mysql'),
        ]);
    }

    public static function record_logout($user_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_login_events';
        $event_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE user_id = %d ORDER BY login_at DESC LIMIT 1",
            $user_id
        ));
        if ($event_id) {
            $wpdb->update(
                $table,
                ['logout_at' => current_time('mysql')],
                ['id' => $event_id]
            );
        }
    }
}
