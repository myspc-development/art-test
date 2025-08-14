<?php
namespace ArtPulse\Audit;

use ArtPulse\Support\WidgetIds;

/**
 * Compute expected vs actual widget rendering parity.
 */
class Parity {
    /**
     * Determine widgets expected to render for a role.
     *
     * @return array<int,string>
     */
    public static function expected_for_role(string $role): array
    {
        $builder    = WidgetSources::get_builder_layout($role);
        $visibility = WidgetSources::get_visibility_roles();
        $registry   = WidgetSources::get_registry();
        $expected   = [];
        foreach ($builder as $id) {
            $allowed = $visibility[$id] ?? [];
            if ($allowed && !in_array($role, $allowed, true)) {
                continue;
            }
            $status = $registry[$id]['status'] ?? 'active';
            if ($status !== 'active' && !(defined('AP_STRICT_FLAGS') && AP_STRICT_FLAGS)) {
                continue;
            }
            $expected[] = $id;
        }
        return $expected;
    }

    /**
     * Compare expected results with actual AuditBus snapshot.
     */
    public static function compare_with_actual(string $role): array
    {
        $expected = self::expected_for_role($role);
        $events   = AuditBus::snapshot();
        $rendered = [];
        $last     = [];
        foreach ($events as $e) {
            if (($e['role'] ?? '') !== $role || ($e['type'] ?? '') !== 'render') {
                continue;
            }
            $id = WidgetIds::canonicalize($e['id']);
            if (!empty($e['ok'])) {
                $rendered[] = $id;
            }
            $last[$id] = $e;
        }
        $rendered = array_values(array_unique($rendered));
        $missing = [];
        foreach ($expected as $id) {
            if (!in_array($id, $rendered, true)) {
                $missing[$id] = $last[$id]['reason'] ?? 'not_rendered';
            }
        }
        $extra = array_values(array_diff($rendered, $expected));

        $problems = self::problems();
        return [
            'would_render' => $expected,
            'did_render'   => $rendered,
            'missing'      => $missing,
            'extra'        => $extra,
            'problems'     => $problems,
        ];
    }

    /**
     * Detect configuration problems independent of rendering parity.
     *
     * @return array<string,string> Map of widget id to problem reason.
     */
    public static function problems(): array
    {
        $registry = WidgetSources::get_registry();
        $problems = [];
        foreach ($registry as $id => $info) {
            if (!($info['callback_is_callable'] ?? false) || in_array($info['status'] ?? '', ['coming_soon', 'inactive'], true)) {
                $problems[$id] = 'missing_callback';
            }
            if (!empty($info['is_placeholder'])) {
                $problems[$id] = 'placeholder_renderer';
            }
        }
        return $problems;
    }
}
