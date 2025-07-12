<?php
namespace ArtPulse\Admin;
use ArtPulse\Admin\ImportExportTab;
use ArtPulse\Admin\SettingsRegistry;
use ArtPulse\Admin\FieldRenderer;
use ArtPulse\Admin\ConfigBackupTab;
use ArtPulse\Admin\UpdatesTab;
use ArtPulse\Admin\DashboardWidgetTools;
use ArtPulse\Core\ActivityLogger;

class SettingsPage
{
    public static function register()
    {
        self::bootstrap_settings();
        ConfigBackupTab::register();
        UpdatesTab::register();
        DashboardWidgetTools::register();
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
        SettingsRegistry::register_tab('config_backup', __('Config Backup', 'artpulse'));
        SettingsRegistry::register_tab('shortcodes', __('Shortcode Pages', 'artpulse'));
        SettingsRegistry::register_tab('search', __('Search', 'artpulse'));
        SettingsRegistry::register_tab('emails', __('Email Delivery', 'artpulse'));
        SettingsRegistry::register_tab('updates', __('Updates', 'artpulse'));

        $general_fields = [
            'basic_fee' => [
                'label' => __('Basic Member Fee ($)', 'artpulse'),
                'desc'  => __('Monthly cost for Basic members. Leave blank to disable.', 'artpulse'),
                'type'  => 'text',
            ],
            'pro_fee' => [
                'label' => __('Pro Artist Fee ($)', 'artpulse'),
                'desc'  => __('Subscription price for Pro Artists.', 'artpulse'),
                'type'  => 'text',
            ],
            'org_fee' => [
                'label' => __('Organization Fee ($)', 'artpulse'),
                'desc'  => __('Fee charged to organizations.', 'artpulse'),
                'type'  => 'text',
            ],
            'currency' => [
                'label' => __('Currency (ISO)', 'artpulse'),
                'desc'  => __('3-letter currency code (e.g., USD, EUR, GBP).', 'artpulse'),
                'type'  => 'text',
            ],
            'stripe_enabled' => [
                'label' => __('Enable Stripe Integration', 'artpulse'),
                'desc'  => __('Enable Stripe to manage payments and subscriptions.', 'artpulse'),
                'type'  => 'checkbox',
            ],
            'stripe_pub_key' => [
                'label' => __('Stripe Publishable Key', 'artpulse'),
                'desc'  => __('Used for client-side Stripe operations.', 'artpulse'),
                'type'  => 'text',
            ],
            'stripe_secret' => [
                'label' => __('Stripe Secret Key', 'artpulse'),
                'desc'  => __('Used for secure server-side API calls to Stripe.', 'artpulse'),
                'type'  => 'text',
            ],
            'stripe_webhook_secret' => [
                'label' => __('Stripe Webhook Secret', 'artpulse'),
                'desc'  => __('Secret used to verify webhook calls from Stripe.', 'artpulse'),
                'type'  => 'text',
            ],
            'woocommerce_enabled' => [
                'label' => __('Enable WooCommerce Integration', 'artpulse'),
                'desc'  => __('Use WooCommerce products for membership purchases.', 'artpulse'),
                'type'  => 'checkbox',
            ],
            'payment_metrics_cache' => [
                'label' => __('Payment Metrics Cache (minutes)', 'artpulse'),
                'desc'  => __('How long to cache payment analytics data.', 'artpulse'),
                'type'  => 'number',
            ],
            'service_worker_enabled' => [
                'label' => __('Enable Service Worker', 'artpulse'),
                'desc'  => __('Adds a service worker for basic offline caching.', 'artpulse'),
                'type'  => 'checkbox',
            ],
            'openai_api_key' => [
                'label' => __('OpenAI API Key', 'artpulse'),
                'desc'  => __('Used for auto-tagging and summaries.', 'artpulse'),
                'type'  => 'text',
            ],
            'oauth_google_enabled' => [
                'label' => __('Enable Google Login', 'artpulse'),
                'desc'  => __('Show Google button on the login form.', 'artpulse'),
                'type'  => 'checkbox',
            ],
            'oauth_facebook_enabled' => [
                'label' => __('Enable Facebook Login', 'artpulse'),
                'desc'  => __('Show Facebook button on the login form.', 'artpulse'),
                'type'  => 'checkbox',
            ],
            'oauth_apple_enabled' => [
                'label' => __('Enable Apple Login', 'artpulse'),
                'desc'  => __('Show Apple button on the login form.', 'artpulse'),
                'type'  => 'checkbox',
            ],
            'enforce_two_factor' => [
                'label' => __('Enforce Two-Factor', 'artpulse'),
                'desc'  => __('Require users to enable two-factor authentication before logging in.', 'artpulse'),
                'type'  => 'checkbox',
            ],
            'override_artist_membership' => [
                'label' => __('Override Artist Membership', 'artpulse'),
                'desc'  => __('Allow administrators to bypass membership requirements and fees for artists.', 'artpulse'),
                'type'  => 'checkbox',
            ],
            'override_org_membership' => [
                'label' => __('Override Organization Membership', 'artpulse'),
                'desc'  => __('Allow administrators to bypass membership requirements and fees for organizations.', 'artpulse'),
                'type'  => 'checkbox',
            ],
            'override_member_membership' => [
                'label' => __('Override Member Membership', 'artpulse'),
                'desc'  => __('Allow administrators to bypass membership requirements and fees for regular members.', 'artpulse'),
                'type'  => 'checkbox',
            ],
            'auto_expire_events' => [
                'label' => __('Auto-expire Past Events', 'artpulse'),
                'desc'  => __('Move events to Draft when the end date has passed.', 'artpulse'),
                'type'  => 'checkbox',
            ],
            'enable_artworks_for_sale' => [
                'label' => __('Enable Artworks for Sale', 'artpulse'),
                'desc'  => __('Allow artworks to be marked for sale.', 'artpulse'),
                'type'  => 'checkbox',
            ],
            'disable_styles' => [
                'label' => __('Disable Plugin Styles', 'artpulse'),
                'desc'  => __('Do not load ArtPulse CSS on the frontend.', 'artpulse'),
                'type'  => 'checkbox',
            ],
            'default_rsvp_limit' => [
                'label' => __('Default RSVP Limit', 'artpulse'),
                'desc'  => __('Pre-filled limit for new events.', 'artpulse'),
                'type'  => 'number',
            ],
            'min_rsvp_limit' => [
                'label' => __('Minimum RSVP Limit', 'artpulse'),
                'desc'  => __('Lowest allowed RSVP limit.', 'artpulse'),
                'type'  => 'number',
            ],
            'max_rsvp_limit' => [
                'label' => __('Maximum RSVP Limit', 'artpulse'),
                'desc'  => __('Highest allowed RSVP limit.', 'artpulse'),
                'type'  => 'number',
            ],
            'waitlists_enabled' => [
                'label' => __('Enable Waitlists', 'artpulse'),
                'desc'  => __('Allow events to use waitlists.', 'artpulse'),
                'type'  => 'checkbox',
            ],
            'default_email_template' => [
                'label' => __('Default Email Template', 'artpulse'),
                'desc'  => __('HTML with placeholders like {{content}}', 'artpulse'),
                'type'  => 'textarea',
            ],
            'keep_data_on_uninstall' => [
                'label' => __('Keep Data on Uninstall', 'artpulse'),
                'desc'  => __('Preserve settings and custom tables when removing the plugin.', 'artpulse'),
                'type'  => 'checkbox',
            ],
        ];

        foreach ($general_fields as $key => $cfg) {
            SettingsRegistry::register_field('general', $key, $cfg);
        }

        $search_fields = [
            'search_service' => [
                'label'   => __('Search Provider', 'artpulse'),
                'desc'    => __('Select Algolia or ElasticPress.', 'artpulse'),
                'type'    => 'select',
                'options' => [
                    ''             => __('None', 'artpulse'),
                    'algolia'      => 'Algolia',
                    'elasticpress' => 'ElasticPress'
                ],
            ],
            'algolia_app_id' => [
                'label' => __('Algolia App ID', 'artpulse'),
                'desc'  => __('Your Algolia application ID.', 'artpulse'),
                'type'  => 'text',
            ],
            'algolia_api_key' => [
                'label' => __('Algolia API Key', 'artpulse'),
                'desc'  => __('Search-only API key.', 'artpulse'),
                'type'  => 'text',
            ],
            'elasticpress_host' => [
                'label' => __('ElasticPress Host', 'artpulse'),
                'desc'  => __('Elasticsearch endpoint URL.', 'artpulse'),
                'type'  => 'text',
            ],
        ];
        foreach ($search_fields as $key => $cfg) {
            SettingsRegistry::register_field('search', $key, $cfg);
        }

        $location_fields = [
            'geonames_username' => [
                'label' => __('Geonames Username', 'artpulse'),
                'desc'  => __('Username for querying the Geonames API.', 'artpulse'),
                'type'  => 'text',
            ],
            'google_places_key' => [
                'label' => __('Google Places API Key', 'artpulse'),
                'desc'  => __('Key for Google Places requests.', 'artpulse'),
                'type'  => 'text',
            ],
        ];
        foreach ($location_fields as $key => $cfg) {
            SettingsRegistry::register_field('location', $key, $cfg);
        }

        $email_fields = [
            'email_method' => [
                'label'   => __('Email Method', 'artpulse'),
                'type'    => 'select',
                'options' => [
                    'wp_mail' => 'wp_mail',
                    'mailgun' => 'mailgun',
                    'sendgrid' => 'sendgrid'
                ],
            ],
            'mailgun_api_key' => [
                'label' => __('Mailgun API Key', 'artpulse'),
                'type'  => 'text',
            ],
            'mailgun_domain' => [
                'label' => __('Mailgun Domain', 'artpulse'),
                'type'  => 'text',
            ],
            'sendgrid_api_key' => [
                'label' => __('SendGrid API Key', 'artpulse'),
                'type'  => 'text',
            ],
            'mailchimp_api_key' => [
                'label' => __('Mailchimp API Key', 'artpulse'),
                'type'  => 'text',
            ],
            'mailchimp_list_id' => [
                'label' => __('Mailchimp List ID', 'artpulse'),
                'type'  => 'text',
            ],
            'email_from_name' => [
                'label' => __('From Name', 'artpulse'),
                'type'  => 'text',
            ],
            'email_from_address' => [
                'label' => __('From Address', 'artpulse'),
                'type'  => 'text',
            ],
        ];
        foreach ($email_fields as $key => $cfg) {
            SettingsRegistry::register_field('emails', $key, $cfg);
        }

        $update_fields = [
            'update_method' => [
                'label'   => __('Update Method', 'artpulse'),
                'type'    => 'select',
                'options' => [
                    'auto' => __('Auto-detect', 'artpulse'),
                    'zip'  => __('ZIP download', 'artpulse'),
                    'git'  => __('Git pull', 'artpulse'),
                ],
            ],
            'github_repo' => [
                'label' => __('GitHub Repo (owner/repo)', 'artpulse'),
                'type'  => 'text',
            ],
            'update_repo_url' => [
                'label' => __('Repository URL', 'artpulse'),
                'desc'  => __('GitHub repository to pull updates from.', 'artpulse'),
                'type'  => 'text',
            ],
            'update_branch' => [
                'label' => __('Branch/Release', 'artpulse'),
                'desc'  => __('Branch or release to track.', 'artpulse'),
                'type'  => 'text',
            ],
            'update_access_token' => [
                'label' => __('Access Token', 'artpulse'),
                'desc'  => __('Personal access token for private repos.', 'artpulse'),
                'type'  => 'text',
            ],
            'auto_update_enabled' => [
                'label' => __('Auto-Update', 'artpulse'),
                'desc'  => __('Check and apply updates daily.', 'artpulse'),
                'type'  => 'checkbox',
            ],
        ];
        foreach ($update_fields as $key => $cfg) {
            SettingsRegistry::register_field('updates', $key, $cfg);
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
        add_submenu_page(
            'artpulse-settings',
            __('Dashboard Widgets', 'artpulse'),
            __('Dashboard Widgets', 'artpulse'),
            'manage_options',
            'artpulse-dashboard-widgets',
            [self::class, 'renderDashboardWidgetsPage']
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
        if (class_exists('\\ArtPulse\\Core\\ActivityLogger')) {
            ActivityLogger::log(
                intval(get_user_meta($user->ID, 'ap_organization_id', true)),
                $user->ID,
                'login',
                'User logged in'
            );
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
            if (class_exists('\\ArtPulse\\Core\\ActivityLogger')) {
                ActivityLogger::log(
                    intval(get_user_meta($user_id, 'ap_organization_id', true)),
                    $user_id,
                    'logout',
                    'User logged out'
                );
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
                    <?php elseif ($slug === 'config_backup') : ?>
                        <?php ConfigBackupTab::render(); ?>
                    <?php elseif ($slug === 'updates') : ?>
                        <form method="post" action="options.php">
                            <?php settings_fields('artpulse_settings_group'); ?>
                            <?php do_settings_sections('artpulse-updates'); ?>
                            <?php submit_button(); ?>
                        </form>
                        <?php include ARTPULSE_PLUGIN_DIR . 'templates/admin/settings-tab-updates.php'; ?>
                    <?php elseif ($slug === 'social_auto') : ?>
                        <?php \ArtPulse\Integration\SocialAutoPoster::render_settings(); ?>
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
                $config['key'] = $key;
                add_settings_field(
                    $key,
                    $config['label'],
                    [self::class, 'renderField'],
                    'artpulse-' . $slug,
                    $section,
                    [
                        'label_for'   => $key,
                        'description' => $config['desc'] ?? '',
                        'field'       => $config,
                        'tab'         => $slug,
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
                'keep_data_on_uninstall',
                'oauth_google_enabled',
                'oauth_facebook_enabled',
                'oauth_apple_enabled',
                'enforce_two_factor',
                'auto_update_enabled'
            ])) {
                $output[$key] = isset($value) ? 1 : 0;
            } elseif ($key === 'payment_metrics_cache' || in_array($key, ['default_rsvp_limit', 'min_rsvp_limit', 'max_rsvp_limit'])) {
                $output[$key] = absint($value);
            } elseif ($key === 'search_service') {
                $allowed = ['algolia', 'elasticpress'];
                $output[$key] = in_array($value, $allowed, true) ? $value : '';
            } elseif ($key === 'email_method') {
                $allowed = ['wp_mail', 'mailgun', 'sendgrid'];
                $output[$key] = in_array($value, $allowed, true) ? $value : 'wp_mail';
            } elseif ($key === 'update_method') {
                $allowed = ['zip', 'git', 'auto'];
                $output[$key] = in_array($value, $allowed, true) ? $value : 'auto';
            } elseif ($key === 'email_from_address') {
                $output[$key] = sanitize_email($value);
            } elseif (in_array($key, ['mailgun_api_key', 'mailgun_domain', 'sendgrid_api_key', 'mailchimp_api_key', 'mailchimp_list_id', 'email_from_name', 'openai_api_key'], true)) {
                $output[$key] = sanitize_text_field($value);
            } elseif ($key === 'default_email_template') {
                $output[$key] = sanitize_textarea_field($value);
            } else {
                $output[$key] = sanitize_text_field($value);
            }
        }
        return $output;
    }
    public static function renderField($args)
    {
        if (!isset($args['field'])) {
            return;
        }

        FieldRenderer::render($args['field'], $args['tab'] ?? '');
    }

    public static function renderDashboardWidgetsPage(): void
    {
        echo '<div id="ap-dashboard-widgets-canvas"></div>';
        echo '<noscript><div class="notice notice-error"><p>' . esc_html__(
            'This page requires JavaScript to manage dashboard widgets.',
            'artpulse'
        ) . '</p></div></noscript>';
        wp_enqueue_script(
            'ap-widget-editor',
            plugin_dir_url(ARTPULSE_PLUGIN_FILE) . 'assets/js/widget-editor.js',
            ['sortablejs'],
            '1.0',
            true
        );
        wp_localize_script(
            'ap-widget-editor',
            'apWidget',
            [
                'nonce' => wp_create_nonce('ap_save_widget_layout'),
            ]
        );
        DashboardWidgetTools::render();
    }
}