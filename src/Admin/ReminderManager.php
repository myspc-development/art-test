<?php
namespace ArtPulse\Admin;

/**
 * Schedules and sends event reminders.
 */
class ReminderManager
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/admin/reminders', [
            'methods'  => ['GET', 'POST'],
            'callback' => [self::class, 'handle'],
            'permission_callback' => [self::class, 'check_permission'],
        ]);
    }

    public static function check_permission(): bool
    {
        return current_user_can('manage_options');
    }

    public static function handle(\WP_REST_Request $request)
    {
        return rest_ensure_response(['status' => 'reminder placeholder']);
    }
}
