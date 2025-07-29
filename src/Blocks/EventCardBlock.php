<?php
namespace ArtPulse\Blocks;

use ArtPulse\Frontend\EventCardShortcode;

class EventCardBlock {
    public static function register(): void {
        add_action('init', [self::class, 'register_block']);
    }

    public static function register_block(): void {
        if (!function_exists('register_block_type_from_metadata')) {
            return;
        }
        register_block_type_from_metadata(__DIR__ . '/../../blocks/event-card', [
            'render_callback' => [self::class, 'render_callback'],
        ]);
    }

    public static function render_callback($attributes) {
        $id = intval($attributes['id'] ?? 0);
        return EventCardShortcode::render(['id' => $id]);
    }
}
