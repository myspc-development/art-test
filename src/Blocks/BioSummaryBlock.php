<?php
namespace ArtPulse\Blocks;

class BioSummaryBlock
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

        register_block_type('artpulse/bio-summary', [
            'editor_script'   => 'artpulse-bio-summary-block',
            'render_callback' => [self::class, 'render_callback'],
            'attributes'      => [
                'summary' => ['type' => 'string', 'default' => ''],
            ],
        ]);

        wp_register_script(
            'artpulse-bio-summary-block',
            plugins_url('assets/js/bio-summary.js', ARTPULSE_PLUGIN_FILE),
            ['wp-blocks', 'wp-element', 'wp-components', 'wp-data', 'wp-api-fetch', 'wp-block-editor'],
            filemtime(plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'assets/js/bio-summary.js')
        );
    }

    public static function render_callback(array $attributes): string
    {
        $summary = trim($attributes['summary'] ?? '');
        return $summary !== '' ? '<p>' . esc_html($summary) . '</p>' : '';
    }
}
