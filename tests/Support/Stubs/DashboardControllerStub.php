<?php
declare(strict_types=1);

namespace ArtPulse\Tests\Stubs;

/**
 * Test stub for the production DashboardController.
 * Provides a deterministic role resolver for unit tests (no WP required).
 *
 * Mapped to \ArtPulse\Core\DashboardController via class_alias from the
 * unit bootstrap so production code transparently uses this during unit tests.
 */
final class DashboardControllerStub
{
    /** @var string */
    private static string $defaultRole = 'member';

    /**
     * Default widgets available to each role.
     *
     * @var array<string,string[]>
     */
    public static array $role_widgets = [
        'member' => [
            'widget_news',
            'widget_membership',
            'widget_upgrade',
            'widget_account_tools',
            'widget_recommended_for_you',
            'widget_my_rsvps',
            'widget_favorites',
            'widget_local_events',
            'widget_my_follows',
            'widget_notifications',
            'widget_messages',
            'widget_dashboard_feedback',
            'widget_cat_fact',
        ],
        'artist' => [
            'widget_artist_feed_publisher',
            'widget_artist_audience_insights',
            'widget_artist_spotlight',
            'widget_artist_revenue_summary',
            'widget_my_events',
            'widget_messages',
            'widget_notifications',
            'widget_dashboard_feedback',
            'widget_cat_fact',
        ],
        'organization' => [
            'widget_org_event_overview',
            'widget_artpulse_analytics_widget',
            'widget_org_ticket_insights',
            'widget_my_events',
            'widget_org_team_roster',
            'widget_audience_crm',
            'widget_org_broadcast_box',
            'widget_org_approval_center',
            'widget_webhooks',
            'widget_support_history',
        ],
        'administrator' => [],
    ];

    /**
     * Default presets used by the CLI checker.
     *
     * @var array<string,array{role:string,layout:array<int,array{id:string}>}>
     */
    private static array $presets = [];

    /**
     * Allow tests to override the default role used when we cannot derive one.
     */
    public static function set_default_role(string $role): void
    {
        self::$defaultRole = self::sanitize($role);
    }

    /**
     * Production code expects: DashboardController::get_role(int $user_id): string
     */
    public static function get_role(int $user_id = 0): string
    {
        // If WP is loaded (integration tests), prefer actual user roles.
        if (\function_exists('get_userdata')) {
            $uid = $user_id;
            if (!$uid && \function_exists('get_current_user_id')) {
                $uid = (int) \get_current_user_id();
            }
            if ($uid > 0) {
                $u = \get_userdata($uid);
                if ($u && !empty($u->roles) && \is_array($u->roles)) {
                    $first = (string) \reset($u->roles);
                    if ($first !== '') {
                        return self::sanitize($first);
                    }
                }
            }
        }

        // Honor an env override for deterministic unit tests.
        $forced = \getenv('AP_TEST_ROLE');
        if (\is_string($forced) && $forced !== '') {
            return self::sanitize($forced);
        }

        return self::$defaultRole;
    }

    /**
     * Allow tests to define the presets returned from get_default_presets().
     */
    public static function set_presets(array $presets): void
    {
        self::$presets = $presets;
    }

    /**
     * Minimal implementation mirroring the production method.
     */
    public static function get_default_presets(): array
    {
        return self::$presets;
    }

    /**
     * Return the widgets configured for a role.
     *
     * @return array<int,string>
     */
    public static function get_widgets_for_role(string $role): array
    {
        $role = self::sanitize($role);
        return self::$role_widgets[$role] ?? [];
    }

    /**
     * Determine the dashboard layout for a user.
     */
    public static function get_user_dashboard_layout(int $user_id, ?string $preview_role = null): array
    {
        if (
            isset($_GET['ap_preview_user'], $_GET['ap_preview_nonce']) &&
            \function_exists('current_user_can') && \current_user_can('manage_options')
        ) {
            $nonce = self::sanitize((string) $_GET['ap_preview_nonce']);
            if (!\function_exists('wp_verify_nonce') || \wp_verify_nonce($nonce, 'ap_preview')) {
                $preview = (int) $_GET['ap_preview_user'];
                if ($preview > 0) {
                    $user_id = $preview;
                }
            }
        }

        $role   = $preview_role ? self::sanitize($preview_role) : self::get_role($user_id);
        $custom = \get_user_meta($user_id, 'ap_dashboard_layout', true);
        $layout = [];

        if (\is_array($custom) && !empty($custom)) {
            $layout = $custom;
        } else {
            $layouts = \get_option('ap_dashboard_widget_config', []);
            if (isset($layouts[$role]) && \is_array($layouts[$role])) {
                $layout = $layouts[$role];
            } else {
                $layout = array_map(
                    static fn($id) => ['id' => $id, 'visible' => true],
                    self::get_widgets_for_role($role)
                );
            }
        }

        $out = [];
        foreach ($layout as $entry) {
            if (\is_array($entry) && isset($entry['id'])) {
                $out[] = [
                    'id'      => self::sanitize((string) $entry['id']),
                    'visible' => isset($entry['visible']) ? (bool) $entry['visible'] : true,
                ];
            } elseif (\is_string($entry) && $entry !== '') {
                $out[] = ['id' => self::sanitize($entry), 'visible' => true];
            }
        }

        if (empty($out)) {
            $out[] = ['id' => 'empty_dashboard', 'visible' => true];
        }

        return $out;
    }

    /**
     * Minimal role resolution mirroring production query handling.
     */
    public static function resolveRoleIntoQuery(\WP_Query $q): void
    {
        $req  = isset($_GET['role']) ? self::sanitize((string) $_GET['role']) : self::sanitize((string) $q->get('role'));
        $role = \in_array($req, ['member', 'artist', 'organization'], true) ? $req : 'member';

        if (\function_exists('set_query_var')) {
            \set_query_var('ap_role', $role);
        } else {
            $GLOBALS['ap_role'] = $role;
        }

        if (\function_exists('add_action')) {
            \add_action('send_headers', static function () use ($role): void {
                if (\function_exists('header')) {
                    \header('X-AP-Resolved-Role: ' . $role);
                }
            });
        } else {
            $GLOBALS['mock_send_headers'][] = static function () use ($role): void {
                if (\function_exists('header')) {
                    \header('X-AP-Resolved-Role: ' . $role);
                }
            };
        }
    }

    /**
     * Mirror the production interceptTemplate logic without depending on
     * WordPress. Uses globals and MockStorage state that tests can control.
     */
    public static function interceptTemplate(string $template): string
    {
        // Determine whether a dashboard request is being made either through
        // the special query var or by visiting the dashboard slug.
        $isQuery = isset($_GET['ap_dashboard']) && $_GET['ap_dashboard'] === '1';

        $isPage = isset($GLOBALS['mock_is_page_dashboard'])
            ? (bool) $GLOBALS['mock_is_page_dashboard']
            : false;

        // Authentication and capability checks can also be toggled by tests.
        $isLoggedIn = isset($GLOBALS['mock_is_user_logged_in'])
            ? (bool) $GLOBALS['mock_is_user_logged_in']
            : true;

        $hasCap = \in_array('view_artpulse_dashboard', MockStorage::$current_roles, true);

        if (($isQuery || $isPage) && $isLoggedIn && $hasCap) {
            $base = defined('ARTPULSE_PLUGIN_DIR')
                ? ARTPULSE_PLUGIN_DIR
                : (defined('ARTPULSE_PLUGIN_FILE') && \function_exists('plugin_dir_path')
                    ? \plugin_dir_path(ARTPULSE_PLUGIN_FILE)
                    : '');

            $tpl = rtrim($base, '/\\') . '/templates/simple-dashboard.php';
            if (is_file($tpl)) {
                return $tpl;
            }
        }

        return $template;
    }
    /**
     * Minimal sanitize_key fallback (don’t assume WP is loaded in unit tests).
     */
    private static function sanitize(string $value): string
    {
        $value = \strtolower($value);
        // keep a-z, 0-9, _, -
        return (string) \preg_replace('/[^a-z0-9_\-]/', '', $value);
    }
}

