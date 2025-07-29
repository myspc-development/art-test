<?php
namespace ArtPulse\Blocks;

use ArtPulse\Frontend\EventListShortcode;

class EventListBlock {
    public static function register(): void {
        add_action('init', [self::class, 'register_block']);
    }

    public static function register_block(): void {
        if (!function_exists('register_block_type_from_metadata')) {
            return;
        }
        register_block_type_from_metadata(__DIR__ . '/../../blocks/event-list', [
            'render_callback' => [self::class, 'render_callback'],
        ]);
    }

    public static function render_callback($attributes) {
        return EventListShortcode::render($attributes);
    }
}
