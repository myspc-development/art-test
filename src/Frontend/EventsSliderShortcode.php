<?php
namespace ArtPulse\Frontend;

class EventsSliderShortcode
{
    public static function register(): void
    {
        add_shortcode('ap_events_slider', [self::class, 'render']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue']);
    }

    public static function enqueue(): void
    {
        if (function_exists('ap_enqueue_global_styles')) {
            ap_enqueue_global_styles();
        }
        $plugin_url = plugin_dir_url(ARTPULSE_PLUGIN_FILE);
        wp_enqueue_style(
            'swiper-css',
            $plugin_url . 'assets/libs/swiper/swiper-bundle.min.css',
            [],
            null
        );
        wp_enqueue_style(
            'ap-swiper-shortcodes',
            $plugin_url . 'assets/css/swiper-shortcodes.css',
            ['swiper-css'],
            filemtime(plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'assets/css/swiper-shortcodes.css')
        );
        wp_enqueue_script(
            'swiper-js',
            $plugin_url . 'assets/libs/swiper/swiper-bundle.min.js',
            [],
            null,
            true
        );
        wp_enqueue_script(
            'ap-events-slider',
            $plugin_url . 'assets/js/ap-events-slider.js',
            ['swiper-js', 'wp-api-fetch'],
            '1.0.0',
            true
        );
        wp_localize_script('ap-events-slider', 'APEventsSlider', [
            'nonce'    => wp_create_nonce('wp_rest'),
            'endpoint' => rest_url('artpulse/v1/events'),
        ]);
    }

    public static function render(): string
    {
        ob_start();
        ?>
        <div class="ap-events-slider swiper">
            <div class="swiper-wrapper"></div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
        <?php
        return ob_get_clean();
    }
}
