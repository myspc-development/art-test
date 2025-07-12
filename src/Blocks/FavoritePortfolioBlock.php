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

        register_block_type('artpulse/favorite-portfolio', [
            'editor_script'   => 'artpulse-favorite-portfolio-block',
            'render_callback' => [self::class, 'render_callback'],
            'attributes'      => [
                'category' => ['type' => 'string'],
                'limit'    => ['type' => 'number', 'default' => 12],
                'sort'     => ['type' => 'string', 'default' => 'date'],
                'page'     => ['type' => 'number', 'default' => 1],
            ],
        ]);

        wp_register_script(
            'artpulse-favorite-portfolio-block',
            plugins_url('assets/js/favorite-portfolio-block.js', ARTPULSE_PLUGIN_FILE),
            ['wp-blocks', 'wp-element', 'wp-i18n'],
            filemtime(plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'assets/js/favorite-portfolio-block.js')
        );
    }

    public static function render_callback(array $attrs): string {
        return ap_render_favorite_portfolio($attrs);
    }
}
