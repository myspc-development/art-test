<?php
namespace ArtPulse\CLI;

if (!defined('ABSPATH')) { exit; }

use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;
use WP_CLI;
use function WP_CLI\Utils\format_items;

/**
 * WP-CLI command to inspect dashboard layouts and widget registration.
 */
class DebugDashboardCommand {
    /**
     * Debug dashboard layout for a user and role.
     *
     * ## OPTIONS
     *
     * [--user=<id>]
     * : User ID to inspect. Defaults to the current user.
     *
     * [--role=<slug>]
     * : Override role resolution for preview/debug.
     *
     * ## EXAMPLES
     *
     *     wp artpulse debug-dashboard
     *     wp artpulse debug-dashboard --user=123
     *     wp artpulse debug-dashboard --user=123 --role=artist
     */
    public function __invoke(array $args, array $assoc_args): void {
        $user_id = isset($assoc_args['user']) ? (int) $assoc_args['user'] : get_current_user_id();
        if (!$user_id) {
            WP_CLI::error('Unable to determine user ID. Use --user to specify one.');
        }

        $role = isset($assoc_args['role'])
            ? sanitize_key($assoc_args['role'])
            : DashboardController::get_role($user_id);

        WP_CLI::log(sprintf('User ID: %d', $user_id));
        WP_CLI::log(sprintf('Role: %s', $role));

        $raw_layout = DashboardController::get_user_dashboard_layout($user_id);

        // Retrieve preset layout for role.
        $presets = DashboardController::get_default_presets();
        $preset_layout = [];
        foreach ($presets as $preset) {
            if (($preset['role'] ?? '') === $role) {
                $preset_layout = $preset['layout'];
                break;
            }
        }

        // Filter the raw layout using the controller's helper.
        $filtered_layout = $raw_layout;
        try {
            $method = new \ReflectionMethod(DashboardController::class, 'filter_accessible_layout');
            $method->setAccessible(true);
            /** @var array $filtered_layout */
            $filtered_layout = $method->invoke(null, $raw_layout, $role);
        } catch (\ReflectionException $e) {
            // If reflection fails, fall back to raw layout.
        }

        $formatter = static function(array $layout): array {
            return array_map(static function($row) {
                return ['id' => $row['id'] ?? ''];
            }, $layout);
        };

        WP_CLI::log('Raw Layout:');
        format_items('table', $formatter($raw_layout), ['id']);

        WP_CLI::log('Filtered Layout:');
        format_items('table', $formatter($filtered_layout), ['id']);

        WP_CLI::log('Preset Layout:');
        format_items('table', $formatter($preset_layout), ['id']);

        $registered = DashboardWidgetRegistry::get_all();
        $missing = [];
        foreach ($filtered_layout as $item) {
            $id = $item['id'] ?? '';
            if ($id && !isset($registered[$id])) {
                $missing[] = $id;
            }
        }

        if ($missing) {
            foreach (array_unique($missing) as $id) {
                WP_CLI::warning("Widget not registered: {$id}");
            }
        } else {
            WP_CLI::success('All widgets are valid.');
        }
    }
}
