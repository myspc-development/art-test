<?php
namespace ArtPulse\Admin;
use ArtPulse\Admin\ImportExportTab;
use ArtPulse\Admin\SettingsRegistry;

class SettingsPage
{
    public static function register()
    {
        self::bootstrap_settings();
        add_action('admin_menu', [self::class, 'addMenu']);
        add_action('admin_init', [self::class, 'registerSettings']);
        add_action('wp_login', [self::class, 'trackLastLogin'], 10, 2);
        add_action('wp_logout', [self::class, 'trackLastLogout']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueueAdminAssets']);
    }

    private static function bootstrap_settings(): void
    {
        SettingsRegistry::register_tab('general', __('General', 'artpulse'));
        SettingsRegistry::register_tab('location', __('Location APIs', 'artpulse'));
        SettingsRegistry::register_tab('import_export', __('Import/Export', 'artpulse'));
        SettingsRegistry::register_tab('shortcodes', __('Shortcode Pages', 'artpulse'));

        $general_fields = [
            'basic_fee' => [
                'label' => __('Basic Member Fee ($)', 'artpulse'),
                'desc'  => __('Monthly cost for Basic members. Leave blank to disable.', 'artpulse'),
            ],
            'pro_fee' => [
                'label' => __('Pro Artist Fee ($)', 'artpulse'),
                'desc'  => __('Subscription price for Pro Artists.', 'artpulse'),
            ],
            'org_fee' => [
                'label' => __('Organization Fee ($)', 'artpulse'),
                'desc'  => __('Fee charged to organizations.', 'artpulse'),
            ],
            'currency' => [
                'label' => __('Currency (ISO)', 'artpulse'),
                'desc'  => __('3-letter currency code (e.g., USD, EUR, GBP).', 'artpulse'),
            ],
            'stripe_enabled' => [
                'label' => __('Enable Stripe Integration', 'artpulse'),
                'desc'  => __('Enable Stripe to manage payments and subscriptions.', 'artpulse'),
            ],
            'stripe_pub_key' => [
                'label' => __('Stripe Publishable Key', 'artpulse'),
                'desc'  => __('Used for client-side Stripe operations.', 'artpulse'),
            ],
            'stripe_secret' => [
                'label' => __('Stripe Secret Key', 'artpulse'),
                'desc'  => __('Used for secure server-side API calls to Stripe.', 'artpulse'),
            ],
            'stripe_webhook_secret' => [
                'label' => __('Stripe Webhook Secret', 'artpulse'),
                'desc'  => __('Secret used to verify webhook calls from Stripe.', 'artpulse'),
            ],
            'payment_metrics_cache' => [
                'label' => __('Payment Metrics Cache (minutes)', 'artpulse'),
                'desc'  => __('How long to cache payment analytics data.', 'artpulse'),
            ],
            'service_worker_enabled' => [
                'label' => __('Enable Service Worker', 'artpulse'),
                'desc'  => __('Adds a service worker for basic offline caching.', 'artpulse'),
            ],
            'oauth_google_enabled' => [
                'label' => __('Enable Google Login', 'artpulse'),
                'desc'  => __('Show Google button on the login form.', 'artpulse'),
            ],
            'oauth_facebook_enabled' => [
                'label' => __('Enable Facebook Login', 'artpulse'),
                'desc'  => __('Show Facebook button on the login form.', 'artpulse'),
            ],
            'oauth_apple_enabled' => [
                'label' => __('Enable Apple Login', 'artpulse'),
                'desc'  => __('Show Apple button on the login form.', 'artpulse'),
            ],
            'enforce_two_factor' => [
                'label' => __('Enforce Two-Factor', 'artpulse'),
                'desc'  => __('Require users to enable two-factor authentication before logging in.', 'artpulse'),
            ],
            'override_artist_membership' => [
                'label' => __('Override Artist Membership', 'artpulse'),
                'desc'  => __('Allow administrators to bypass membership requirements and fees for artists.', 'artpulse'),
            ],
            'override_org_membership' => [
                'label' => __('Override Organization Membership', 'artpulse'),
                'desc'  => __('Allow administrators to bypass membership requirements and fees for organizations.', 'artpulse'),
            ],
            'override_member_membership' => [
                'label' => __('Override Member Membership', 'artpulse'),
                'desc'  => __('Allow administrators to bypass membership requirements and fees for regular members.', 'artpulse'),
            ],
            'auto_expire_events' => [
                'label' => __('Auto-expire Past Events', 'artpulse'),
                'desc'  => __('Move events to Draft when the end date has passed.', 'artpulse'),
            ],
            'enable_artworks_for_sale' => [
                'label' => __('Enable Artworks for Sale', 'artpulse'),
                'desc'  => __('Allow artworks to be marked for sale.', 'artpulse'),
            ],
            'disable_styles' => [
                'label' => __('Disable Plugin Styles', 'artpulse'),
                'desc'  => __('Do not load ArtPulse CSS on the frontend.', 'artpulse'),
            ],
            'default_rsvp_limit' => [
                'label' => __('Default RSVP Limit', 'artpulse'),
                'desc'  => __('Pre-filled limit for new events.', 'artpulse'),
            ],
            'min_rsvp_limit' => [
                'label' => __('Minimum RSVP Limit', 'artpulse'),
                'desc'  => __('Lowest allowed RSVP limit.', 'artpulse'),
            ],
            'max_rsvp_limit' => [
                'label' => __('Maximum RSVP Limit', 'artpulse'),
                'desc'  => __('Highest allowed RSVP limit.', 'artpulse'),
            ],
            'waitlists_enabled' => [
                'label' => __('Enable Waitlists', 'artpulse'),
                'desc'  => __('Allow events to use waitlists.', 'artpulse'),
            ],
            'default_privacy_email' => [
                'label' => __('Default Email Privacy', 'artpulse'),
                'desc'  => __('Public or private visibility for new user emails.', 'artpulse'),
            ],
            'default_privacy_location' => [
                'label' => __('Default Location Privacy', 'artpulse'),
                'desc'  => __('Public or private visibility for new user locations.', 'artpulse'),
            ],
        ];

        foreach ($general_fields as $key => $cfg) {
            SettingsRegistry::register_field('general', $key, $cfg);
        }

        $location_fields = [
            'geonames_username' => [
                'label' => __('Geonames Username', 'artpulse'),
                'desc'  => __('Username for querying the Geonames API.', 'artpulse'),
            ],
            'google_places_key' => [
                'label' => __('Google Places API Key', 'artpulse'),
                'desc'  => __('Key for Google Places requests.', 'artpulse'),
            ],
        ];
        foreach ($location_fields as $key => $cfg) {
            SettingsRegistry::register_field('location', $key, $cfg);
        }
    }
    public static function addMenu()
    {
        add_menu_page(
            __('ArtPulse', 'artpulse'),
            __('ArtPulse', 'artpulse'),
            'manage_options',
            'artpulse-settings',
            [self::class, 'render'],
            'dashicons-admin-generic',
            56
        );
        add_submenu_page(
            'artpulse-settings',
            __('Settings', 'artpulse'),
            __('Settings', 'artpulse'),
            'manage_options',
            'artpulse-settings',
            [self::class, 'render']
        );
        add_submenu_page(
            'artpulse-settings',
            __('Members', 'artpulse'),
            __('Members', 'artpulse'),
            'manage_options',
            'artpulse-members',
            [self::class, 'renderMembersPage']
        );
        add_submenu_page(
            'artpulse-settings',
            __('Engagement Dashboard', 'artpulse'),
            __('Engagement', 'artpulse'),
            'manage_options',
            'artpulse-engagement',
            [EngagementDashboard::class, 'render']
        );
        // Additional admin pages can hook into 'admin_menu' to add more submenus.
    }
    public static function enqueueAdminAssets($hook)
    {
        global $current_screen;
        if (isset($current_screen->id) && $current_screen->id != 'toplevel_page_artpulse-settings') {
            return;
        }
        wp_enqueue_script(
            'chart-js',
            plugins_url('/assets/libs/chart.js/chart.min.js', ARTPULSE_PLUGIN_FILE),
            [],
            null,
            true
        );
        wp_enqueue_script('ap-admin-dashboard', plugins_url('/assets/js/ap-admin-dashboard.js', ARTPULSE_PLUGIN_FILE), ['chart-js'], '1.0', true);
        wp_enqueue_script(
            'ap-settings-tabs',
            plugins_url('/assets/js/ap-settings-tabs.js', ARTPULSE_PLUGIN_FILE),
            [],
            '1.0',
            true
        );
        $signup_data = self::getMonthlySignupsByLevel();
        wp_localize_script('ap-admin-dashboard', 'APAdminStats', $signup_data);
    }
    public static function getMonthlySignupsByLevel()
    {
        global $wpdb;
        $levels = \ArtPulse\Core\MembershipManager::LEVELS;
        $data   = [];
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = date_i18n('M', strtotime("-{$i} months"));
        }
        foreach ($levels as $level) {
            $counts = [];
            for ($i = 0; $i < 6; $i++) {
                $month = date('Y-m-01', strtotime("-{$i} months"));
                $nextMonth = date('Y-m-01', strtotime("-" . ($i - 1) . " months"));
                $users = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT COUNT(*) FROM $wpdb->usermeta AS um
                         JOIN $wpdb->users AS u ON u.ID = um.user_id
                         WHERE um.meta_key = 'ap_membership_level'
                         AND um.meta_value = %s
                         AND u.user_registered >= %s AND u.user_registered < %s",
                        $level, $month, $nextMonth
                    )
                );
                $counts[] = intval($users);
            }
            $data[$level] = array_reverse($counts); // recent months last
        }
        $data['months'] = $months;
        return $data;
    }
    public static function trackLastLogin($user_login, $user)
    {
        update_user_meta($user->ID, 'last_login', current_time('mysql'));
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if (class_exists('\\ArtPulse\\Admin\\LoginEventsPage')) {
            \ArtPulse\Admin\LoginEventsPage::add_event($user->ID, $ip);
        }
    }

    public static function trackLastLogout(): void
    {
        $user_id = get_current_user_id();
        if ($user_id) {
            update_user_meta($user_id, 'last_logout', current_time('mysql'));
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            update_user_meta($user_id, 'last_logout_ip', $ip);
            if (class_exists('\\ArtPulse\\Admin\\LoginEventsPage') && method_exists('\\ArtPulse\\Admin\\LoginEventsPage', 'record_logout')) {
                \ArtPulse\Admin\LoginEventsPage::record_logout($user_id);
            }
        }
    }

    public static function renderMembersPage()
    {
        $search_query = sanitize_text_field($_GET['ap_search'] ?? '');
        $level_filter = sanitize_text_field($_GET['ap_level'] ?? '');
        $level_filter = ucfirst(strtolower($level_filter));
        $args = [
            'search'         => "*{$search_query}*",
            'search_columns' => ['user_login', 'user_email', 'display_name'],
            'orderby'        => 'registered',
            'order'          => 'DESC',
            'number'         => 100,
        ];
        if (!empty($level_filter)) {
            $args['meta_query'] = [[
                'key'   => 'ap_membership_level',
                'value' => $level_filter,
            ]];
        }
        $users = get_users($args);
        // CSV Export
        if (isset($_GET['ap_export_csv'])) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="artpulse-members.csv"');
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Name', 'Email', 'Role', 'Membership Level', 'Submissions', 'Last Login', 'Registered At', 'Expiry']);
            foreach ($users as $user) {
                $level = get_user_meta($user->ID, 'ap_membership_level', true);
                $last_login = get_user_meta($user->ID, 'last_login', true);
                $expires = get_user_meta($user->ID, 'ap_membership_expires', true);
                $registered_at = get_user_meta($user->ID, 'registered_at', true);
                fputcsv($output, [
                    $user->display_name ?: $user->user_login,
                    $user->user_email,
                    implode(', ', $user->roles),
                    $level ?: '—',
                    count_user_posts($user->ID, 'artwork'), // change to match your CPT
                    $last_login ?: '—',
                    $registered_at ?: $user->user_registered,
                    $expires ?: '—',
                ]);
            }
            fclose($output);
            exit;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('ArtPulse Members', 'artpulse'); ?></h1>
            <form method="get" style="margin-bottom: 20px;">
                <input type="hidden" name="page" value="artpulse-members" />
                <input type="text" name="ap_search" placeholder="<?php esc_attr_e('Search users...', 'artpulse'); ?>" value="<?php echo esc_attr($search_query); ?>" />
                <select name="ap_level">
                    <option value=""><?php esc_html_e('All Levels', 'artpulse'); ?></option>
                    <?php foreach (\ArtPulse\Core\MembershipManager::LEVELS as $l): ?>
                        <option value="<?php echo esc_attr($l); ?>" <?php selected($level_filter, $l); ?>><?php echo esc_html($l); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="button"><?php esc_html_e('Filter', 'artpulse'); ?></button>
                <button type="submit" name="ap_export_csv" class="button-secondary"><?php esc_html_e('Export CSV', 'artpulse'); ?></button>
            </form>
            <table class="widefat fixed striped">
                <thead>
                <tr>
                    <th><?php esc_html_e('Name', 'artpulse'); ?></th>
                    <th><?php esc_html_e('Email', 'artpulse'); ?></th>
                    <th><?php esc_html_e('Level', 'artpulse'); ?></th>
                    <th><?php esc_html_e('Submissions', 'artpulse'); ?></th>
                    <th><?php esc_html_e('Last Login', 'artpulse'); ?></th>
                    <th><?php esc_html_e('Registered At', 'artpulse'); ?></th>
                    <th><?php esc_html_e('Expires', 'artpulse'); ?></th>
                    <th><?php esc_html_e('Actions', 'artpulse'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $user):
                    $level     = get_user_meta($user->ID, 'ap_membership_level', true);
                    $last_login = get_user_meta($user->ID, 'last_login', true);
                    $expires       = get_user_meta($user->ID, 'ap_membership_expires', true);
                    $count         = count_user_posts($user->ID, 'artwork'); // change post type if needed
                    $registered_at = get_user_meta($user->ID, 'registered_at', true);
                    ?>
                    <tr>
                        <td><?php echo esc_html($user->display_name ?: $user->user_login); ?></td>
                        <td><?php echo esc_html($user->user_email); ?></td>
                        <td><?php echo esc_html($level ?: '—'); ?></td>
                        <td><?php echo esc_html($count); ?></td>
                        <td><?php echo esc_html($last_login ?: '—'); ?></td>
                        <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($registered_at ?: $user->user_registered))); ?></td>
                        <td><?php echo esc_html($expires ?: '—'); ?></td>
                        <td>
                            <a href="<?php echo esc_url(get_edit_user_link($user->ID)); ?>"><?php esc_html_e('View', 'artpulse'); ?></a>
                            |
                            <a href="<?php echo esc_url(wp_nonce_url(admin_url("users.php?action=resetpassword&user={$user->ID}"), 'reset_user_password_' . $user->ID)); ?>">
                                <?php esc_html_e('Reset Password', 'artpulse'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="8"><?php esc_html_e('No members found.', 'artpulse'); ?></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public static function renderImportExportPage()
    {
        if (isset($_GET['ap_export_posts'])) {
            $type    = sanitize_key($_GET['ap_export_posts']);
            $allowed = ['artpulse_org', 'artpulse_event', 'artpulse_artist', 'artpulse_artwork'];
            if (in_array($type, $allowed, true)) {
                ImportExportTab::exportPostsCsv($type);
            }
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Import/Export', 'artpulse'); ?></h1>
            <?php ImportExportTab::render(); ?>
        </div>
        <?php
    }
    public static function render()
    {
        if (isset($_POST['ap_test_webhook']) && check_admin_referer('ap_test_webhook_action')) {
            $log = get_option('artpulse_webhook_log', []);
            $log[] = ['type' => 'invoice.paid', 'time' => current_time('mysql')];
            if (count($log) > 20) {
                $log = array_slice($log, -20);
            }
            update_option('artpulse_webhook_log', $log);
            update_option('artpulse_webhook_status', 'Simulated');
            update_option('artpulse_webhook_last_event', end($log));
            echo '<div class="notice notice-success"><p>' . esc_html__('Webhook simulated successfully.', 'artpulse') . '</p></div>';
        }
        if (isset($_POST['ap_clear_webhook_log']) && check_admin_referer('ap_clear_webhook_log_action')) {
            delete_option('artpulse_webhook_log');
            echo '<div class="notice notice-success"><p>' . esc_html__('Webhook log cleared.', 'artpulse') . '</p></div>';
        }
        $webhook_status = get_option('artpulse_webhook_status', 'Unknown');
        $last_event     = get_option('artpulse_webhook_last_event', []);
        $log            = get_option('artpulse_webhook_log', []);
        $tabs           = apply_filters('artpulse_settings_tabs', SettingsRegistry::get_tabs());
        $tab_keys       = array_keys($tabs);
        $current_tab    = sanitize_key($_GET['tab'] ?? ($tab_keys[0] ?? 'general'));

        if (isset($_GET['ap_export_posts'])) {
            $type    = sanitize_key($_GET['ap_export_posts']);
            $allowed = ['artpulse_org', 'artpulse_event', 'artpulse_artist', 'artpulse_artwork'];
            if (in_array($type, $allowed, true)) {
                ImportExportTab::exportPostsCsv($type);
            }
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('ArtPulse Settings', 'artpulse'); ?></h1>
            <h2 class="nav-tab-wrapper" id="ap-settings-nav">
                <?php foreach ($tabs as $slug => $label) : ?>
                    <a href="#<?php echo esc_attr($slug); ?>" class="nav-tab<?php echo $current_tab === $slug ? ' nav-tab-active' : ''; ?>" data-tab="<?php echo esc_attr($slug); ?>">
                        <?php echo esc_html($label); ?>
                    </a>
                <?php endforeach; ?>
            </h2>
            <?php foreach ($tabs as $slug => $label) : ?>
                <section id="ap-tab-<?php echo esc_attr($slug); ?>" class="ap-settings-section" data-tab="<?php echo esc_attr($slug); ?>" style="<?php echo $current_tab === $slug ? '' : 'display:none;'; ?>">
                    <?php if ($slug === 'import_export') : ?>
                        <?php ImportExportTab::render(); ?>
                    <?php elseif ($slug === 'shortcodes') : ?>
                        <?php \ArtPulse\Admin\ShortcodePages::render(); ?>
                    <?php else : ?>
                        <form method="post" action="options.php">
                            <?php settings_fields('artpulse_settings_group'); ?>
                            <?php do_settings_sections('artpulse-' . $slug); ?>
                            <?php submit_button(); ?>
                        </form>
                        <?php if ($slug === 'general') : ?>
                            <hr>
                            <h2><?php esc_html_e('System Status', 'artpulse'); ?></h2>
                            <p>
                                <strong><?php esc_html_e('Webhook Status:', 'artpulse'); ?></strong>
                                <?php echo esc_html($webhook_status); ?><br>
                                <strong><?php esc_html_e('Last Webhook Event:', 'artpulse'); ?></strong>
                                <?php echo esc_html($last_event['type'] ?? 'None'); ?><br>
                                <strong><?php esc_html_e('Received At:', 'artpulse'); ?></strong>
                                <?php echo esc_html($last_event['time'] ?? 'N/A'); ?>
                            </p>
                            <h2><?php esc_html_e('Webhook Event Log', 'artpulse'); ?></h2>
                            <table class="widefat fixed striped">
                                <thead>
                                <tr>
                                    <th><?php esc_html_e('Timestamp', 'artpulse'); ?></th>
                                    <th><?php esc_html_e('Event Type', 'artpulse'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                if (empty($log)) {
                                    echo '<tr><td colspan="2">' . esc_html__('No webhook events logged.', 'artpulse') . '</td></tr>';
                                } else {
                                    foreach (array_reverse($log) as $entry) {
                                        echo '<tr>';
                                        echo '<td>' . esc_html($entry['time']) . '</td>';
                                        echo '<td>' . esc_html($entry['type']) . '</td>';
                                        echo '</tr>';
                                    }
                                }
                                ?>
                                </tbody>
                            </table>
                            <form method="post" style="margin-top: 10px;">
                                <?php wp_nonce_field('ap_test_webhook_action'); ?>
                                <input type="submit" name="ap_test_webhook" class="button button-secondary" value="<?php esc_attr_e('Simulate Webhook Event', 'artpulse'); ?>">
                            </form>
                            <form method="post" style="margin-top: 10px;">
                                <?php wp_nonce_field('ap_clear_webhook_log_action'); ?>
                                <input type="submit" name="ap_clear_webhook_log" class="button button-secondary" value="<?php esc_attr_e('Clear Webhook Log', 'artpulse'); ?>">
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
        </div>
        <?php
    }
    public static function registerSettings()
    {
        register_setting(
            'artpulse_settings_group',
            'artpulse_settings',
            ['sanitize_callback' => [self::class, 'sanitizeSettings']]
        );

        $tabs = apply_filters('artpulse_settings_tabs', SettingsRegistry::get_tabs());
        foreach ($tabs as $slug => $label) {
            $section = 'ap_' . $slug . '_section';
            add_settings_section($section, $label, '__return_false', 'artpulse-' . $slug);

            $fields = apply_filters('artpulse_settings_fields_' . $slug, SettingsRegistry::get_fields($slug));
            foreach ($fields as $key => $config) {
                add_settings_field(
                    $key,
                    $config['label'],
                    [self::class, 'renderField'],
                    'artpulse-' . $slug,
                    $section,
                    [
                        'label_for'   => $key,
                        'description' => $config['desc'] ?? ''
                    ]
                );
            }
        }
    }
    public static function sanitizeSettings($input)
    {
        $output = [];
        foreach ($input as $key => $value) {
            if (in_array($key, [
                'stripe_enabled',
                'woocommerce_enabled',
                'debug_logging',
                'service_worker_enabled',
                'override_artist_membership',
                'override_org_membership',
                'override_member_membership',
                'auto_expire_events',
                'enable_artworks_for_sale',
                'disable_styles',
                'waitlists_enabled',
                'oauth_google_enabled',
                'oauth_facebook_enabled',
                'oauth_apple_enabled',
                'enforce_two_factor'
            ])) {
                $output[$key] = isset($value) ? 1 : 0;
            } elseif ($key === 'payment_metrics_cache' || in_array($key, ['default_rsvp_limit', 'min_rsvp_limit', 'max_rsvp_limit'])) {
                $output[$key] = absint($value);
            } else {
                $output[$key] = sanitize_text_field($value);
            }
        }
        return $output;
    }
    public static function renderField($args)
    {
        $options = get_option('artpulse_settings');
        $key     = $args['label_for'];
        $value   = $options[$key] ?? '';
        $desc    = $args['description'] ?? '';
        if (in_array($key, [
            'stripe_enabled',
            'woocommerce_enabled',
            'debug_logging',
            'service_worker_enabled',
            'override_artist_membership',
            'override_org_membership',
            'override_member_membership',
            'auto_expire_events',
            'enable_artworks_for_sale',
            'disable_styles',
            'waitlists_enabled',
            'oauth_google_enabled',
            'oauth_facebook_enabled',
            'oauth_apple_enabled',
            'enforce_two_factor'
        ])) {
            echo '<input type="checkbox" id="' . esc_attr($key) . '" name="artpulse_settings[' . esc_attr($key) . ']" value="1"' . checked(1, $value, false) . ' />';
        } elseif (in_array($key, ['default_rsvp_limit', 'min_rsvp_limit', 'max_rsvp_limit', 'payment_metrics_cache'])) {
            echo '<input type="number" id="' . esc_attr($key) . '" name="artpulse_settings[' . esc_attr($key) . ']" value="' . esc_attr($value) . '" class="regular-text" />';
        } elseif (in_array($key, ['default_privacy_email', 'default_privacy_location'])) {
            echo '<select id="' . esc_attr($key) . '" name="artpulse_settings[' . esc_attr($key) . ']">';
            foreach (["public", "private"] as $opt) {
                echo '<option value="' . esc_attr($opt) . '"' . selected($value, $opt, false) . '>' . esc_html(ucfirst($opt)) . '</option>';
            }
            echo '</select>';
        } else {
            echo '<input type="text" id="' . esc_attr($key) . '" name="artpulse_settings[' . esc_attr($key) . ']" value="' . esc_attr($value) . '" class="regular-text" />';
        }
        if ($desc) {
            echo '<p class="description">' . esc_html($desc) . '</p>';
        }
    }
}