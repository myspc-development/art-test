<?php
namespace ArtPulse\Audit;

/**
 * Lightweight event collector for widget render audits.
 */
class AuditBus {
    /** @var array<int,array<string,mixed>> */
    protected static array $events = [];

    public static function on_attempt(string $id, string $role, array $ctx = []): void
    {
        self::$events[] = [
            'type' => 'attempt',
            'id'   => $id,
            'role' => $role,
            'ctx'  => $ctx,
            't'    => microtime(true),
        ];
    }

    public static function on_rendered(string $id, string $role, int $ms, bool $ok = true, string $reason = ''): void
    {
        $event = [
            'type'   => 'render',
            'id'     => $id,
            'role'   => $role,
            'ms'     => $ms,
            'ok'     => $ok,
            'reason' => $reason,
            't'      => microtime(true),
        ];
        self::$events[] = $event;
        do_action('ap_widget_render_audit', $event);
    }

    /**
     * Retrieve a snapshot of all recorded events.
     *
     * @return array<int,array<string,mixed>>
     */
    public static function snapshot(): array
    {
        return self::$events;
    }

    public static function reset(): void
    {
        self::$events = [];
    }
}
