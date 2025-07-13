<?php
namespace ArtPulse\Blocks;

class PortfolioPreviewBlock {
    public static function register(): void {
        add_action('init', [self::class, 'register_block']);
    }

    public static function register_block(): void {
        if (!function_exists('register_block_type_from_metadata')) {
            return;
        }
        register_block_type_from_metadata(__DIR__ . '/../../blocks/widgets/portfolio-preview');
    }
}
