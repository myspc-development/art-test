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
     * Mirror the production interceptTemplate logic without depending on
     * WordPress. Uses globals and MockStorage state that tests can control.
     */
    public static function interceptTemplate(string $template): string
    {
        // Determine whether a dashboard request is being made either through
        // the special query var or by visiting the dashboard slug.
        $isQuery = false;
        if (isset($_GET['ap_dashboard']) && $_GET['ap_dashboard'] === '1') {
            $isQuery = true;
        } elseif (\function_exists('get_query_var') && \get_query_var('ap_dashboard') === '1') {
            $isQuery = true;
        }

        $isPage = false;
        if (\function_exists('is_page')) {
            $isPage = \is_page('dashboard');
        } elseif (isset($GLOBALS['mock_is_page_dashboard'])) {
            $isPage = (bool) $GLOBALS['mock_is_page_dashboard'];
        }

        // Authentication and capability checks can also be toggled by tests.
        $isLoggedIn = true;
        if (\function_exists('is_user_logged_in')) {
            $isLoggedIn = \is_user_logged_in();
        } elseif (isset($GLOBALS['mock_is_user_logged_in'])) {
            $isLoggedIn = (bool) $GLOBALS['mock_is_user_logged_in'];
        }

        $hasCap = false;
        if (\function_exists('current_user_can')) {
            $hasCap = \current_user_can('view_artpulse_dashboard');
        } else {
            $hasCap = \in_array('view_artpulse_dashboard', MockStorage::$current_roles, true);
        }

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

// In unit tests, production class may not exist. Alias the stub so
// \ArtPulse\Core\DashboardController::get_role() resolves to this implementation.
if (!\class_exists(\ArtPulse\Core\DashboardController::class, false)) {
    \class_alias(
        \ArtPulse\Tests\Stubs\DashboardControllerStub::class,
        \ArtPulse\Core\DashboardController::class
    );
}
