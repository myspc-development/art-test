<?php
namespace ArtPulse\Cli;

use ArtPulse\Audit\WidgetSources;
use ArtPulse\Audit\Parity;
use ArtPulse\Audit\AuditBus;
use ArtPulse\Core\DashboardRenderer;
use ArtPulse\Core\DashboardWidgetRegistry;
use WP_CLI; // phpcs:ignore

/**
 * WP-CLI interface for the Widget Audit Suite.
 */
class WidgetAudit {
    public function register(): void
    {
        if (!class_exists('WP_CLI')) {
            return;
        }
        \WP_CLI::add_command('artpulse audit:widgets', [$this, 'widgets']);
        \WP_CLI::add_command('artpulse audit:visibility', [$this, 'visibility']);
        \WP_CLI::add_command('artpulse audit:builder', [$this, 'builder']);
        \WP_CLI::add_command('artpulse audit:render', [$this, 'render']);
        \WP_CLI::add_command('artpulse audit:fix', [$this, 'fix']);
    }

    public function widgets($args, $assoc)
    {
        $registry = WidgetSources::get_registry();
        $vis      = WidgetSources::get_visibility_matrix();
        $rows = [];
        foreach ($registry as $id => $info) {
            $rows[] = [
                'id'                   => $id,
                'status'               => $info['status'],
                'has_callback'         => $info['callback_is_callable'] ? 'yes' : 'no',
                'roles_from_registry'  => implode(',', $info['roles_from_registry']),
                'roles_from_visibility'=> implode(',', $vis[$id] ?? []),
                'registered_in_code_file' => $info['class'],
            ];
        }
        $format = $assoc['format'] ?? 'table';
        \WP_CLI\Utils::format_items($format, $rows, ['id','status','has_callback','roles_from_registry','roles_from_visibility','registered_in_code_file']);
    }

    public function visibility($args, $assoc)
    {
        $vis = WidgetSources::get_visibility_matrix();
        $reg = WidgetSources::get_registry();
        $rows = [];
        foreach ($vis as $id => $roles) {
            $rows[] = [
                'id'                 => $id,
                'allowed_roles(option)' => implode(',', $roles),
                'registry_roles(if any)' => implode(',', $reg[$id]['roles_from_registry'] ?? []),
                'union'              => implode(',', array_unique(array_merge($roles, $reg[$id]['roles_from_registry'] ?? []))),
                'notes(conflicts)'   => '',
            ];
        }
        $format = $assoc['format'] ?? 'table';
        \WP_CLI\Utils::format_items($format, $rows, ['id','allowed_roles(option)','registry_roles(if any)','union','notes(conflicts)']);
    }

    public function builder($args, $assoc)
    {
        $role = $assoc['role'] ?? '';
        if (!$role) {
            WP_CLI::error('Missing --role parameter');
        }
        $roles = $role === 'all' ? array_keys((new \WP_Roles())->roles) : [$role];
        $reg = WidgetSources::get_registry();
        foreach ($roles as $r) {
            $layout = WidgetSources::get_builder_layout($r);
            $rows = [];
            $i = 1;
            foreach ($layout as $id) {
                $rows[] = [
                    'order'        => $i++,
                    'widget_id'    => $id,
                    'in_registry'  => isset($reg[$id]) ? 'yes' : 'no',
                    'has_callback' => isset($reg[$id]) && $reg[$id]['callback_is_callable'] ? 'yes' : 'no',
                ];
            }
            WP_CLI::line('Role: ' . $r);
            $format = $assoc['format'] ?? 'table';
            \WP_CLI\Utils::format_items($format, $rows, ['order','widget_id','in_registry','has_callback']);
        }
    }

    public function render($args, $assoc)
    {
        $role = $assoc['role'] ?? '';
        if (!$role) {
            WP_CLI::error('Missing --role parameter');
        }
        if (isset($assoc['no-preview'])) {
            add_filter('ap_dashboard_preview_enabled', '__return_false');
        }
        add_filter('ap_dashboard_hidden_widgets', fn($hidden, $r) => [], 10, 2);

        // Ensure we have a user of the desired role
        $user_ids = get_users(['role' => $role, 'number' => 1, 'fields' => 'ID']);
        if ($user_ids) {
            $uid = (int) $user_ids[0];
        } else {
            $uid = wp_insert_user([
                'user_login' => 'audit_' . $role . '_' . time(),
                'user_pass'  => wp_generate_password(),
                'role'       => $role,
            ]);
        }
        wp_set_current_user($uid);

        AuditBus::reset();
        $defs = DashboardWidgetRegistry::get_widgets_by_role($role, $uid);
        foreach (array_keys($defs) as $id) {
            DashboardRenderer::render($id, $uid);
        }
        $report = Parity::compare_with_actual($role);
        WP_CLI::line('EXPECTED: ' . implode(', ', $report['would_render']));
        WP_CLI::line('RENDERED: ' . implode(', ', $report['did_render']));
        if ($report['missing']) {
            WP_CLI::line('MISSING:');
            foreach ($report['missing'] as $id => $reason) {
                WP_CLI::line(" - {$id}: {$reason}");
            }
        }
        if ($report['extra']) {
            WP_CLI::line('EXTRA: ' . implode(', ', $report['extra']));
        }
        WP_CLI::line('COUNTS expected=' . count($report['would_render']) . ' rendered=' . count($report['did_render']) . ' missing=' . count($report['missing']) . ' extra=' . count($report['extra']));
        if ($report['missing'] || $report['extra'] || $report['problems']) {
            WP_CLI::halt(1);
        }
    }

    public function fix($args, $assoc)
    {
        WP_CLI::warning('fix subcommand not implemented.');
    }
}
