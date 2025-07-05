<?php
namespace ArtPulse\Frontend;

class RestListShortcodes
{
    public static function register(): void
    {
        add_shortcode('ap_recommendations', [self::class, 'render_recommendations']);
        add_shortcode('ap_collection', [self::class, 'render_collection']);
    }

    public static function render_recommendations($atts): string
    {
        $atts = shortcode_atts([
            'type'  => 'event',
            'limit' => 6,
        ], $atts, 'ap_recommendations');

        return sprintf(
            '<div class="ap-rest-list ap-recommendations" data-type="%s" data-limit="%d"></div>',
            esc_attr($atts['type']),
            intval($atts['limit'])
        );
    }

    public static function render_collection($atts): string
    {
        $atts = shortcode_atts([
            'id' => 0,
        ], $atts, 'ap_collection');

        $id = intval($atts['id']);
        if (!$id) {
            return '';
        }

        return sprintf(
            '<div class="ap-rest-list ap-collection" data-id="%d"></div>',
            $id
        );
    }
}
