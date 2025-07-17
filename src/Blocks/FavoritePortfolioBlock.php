<?php
namespace ArtPulse\Blocks;

class FavoritePortfolioBlock {
    public static function register(): void {
        add_action('init', [self::class, 'register_block']);
    }

    public static function register_block(): void {
        if (!function_exists('register_block_type')) {
            return;
        }

        register_block_type_from_metadata(
            __DIR__ . '/../../blocks/favorite-portfolio',
            [ 'render_callback' => 'render_favorite_portfolio_block' ]
        );
    }

}
