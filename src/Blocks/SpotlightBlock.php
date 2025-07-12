<?php
namespace ArtPulse\Blocks;

class SpotlightBlock
{
    public static function register(): void
    {
        add_action('init', [self::class, 'register_block']);
    }

    public static function register_block(): void
    {
        if (!function_exists('register_block_type')) {
            return;
        }

        register_block_type('artpulse/spotlights', [
            'editor_script'   => 'artpulse-spotlight-block',
            'render_callback' => [self::class, 'render_callback'],
            'attributes'      => [
                'title'     => ['type' => 'string'],
                'image'     => ['type' => 'string'],
                'visibleTo' => ['type' => 'array', 'default' => ['member', 'artist']],
            ],
        ]);

        wp_register_script(
            'artpulse-spotlight-block',
            plugins_url('assets/js/spotlight-block.js', ARTPULSE_PLUGIN_FILE),
            ['wp-blocks', 'wp-element', 'wp-i18n'],
            filemtime(plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'assets/js/spotlight-block.js')
        );
    }

    public static function render_callback($attributes): string
    {
        if (is_admin()) {
            return sprintf(
                '<div class="ap-spotlight-preview"><strong>%s</strong><br><em>Visible to: %s</em></div>',
                esc_html($attributes['title'] ?? ''),
                isset($attributes['visibleTo']) ? implode(', ', (array) $attributes['visibleTo']) : ''
            );
        }

        $user  = wp_get_current_user();
        $roles = (array) $user->roles;

        if (!array_intersect($roles, (array) ($attributes['visibleTo'] ?? []))) {
            return '';
        }

        return sprintf('<div class="ap-spotlight">%s</div>', esc_html($attributes['title'] ?? ''));
    }
}
