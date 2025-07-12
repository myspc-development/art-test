<?php
namespace ArtPulse\Frontend;

class WidgetEmbedShortcode
{
    public static function register(): void
    {
        add_shortcode('ap_widget', [self::class, 'render']);
        add_action('wp_enqueue_scripts', [self::class, 'maybe_enqueue_styles']);
    }

    public static function maybe_enqueue_styles(): void
    {
        if (is_singular()) {
            $post = get_post();
            if ($post && has_shortcode($post->post_content, 'ap_widget')) {
                wp_enqueue_style(
                    'ap-dashboard-style',
                    plugin_dir_url(ARTPULSE_PLUGIN_FILE) . 'assets/css/dashboard-widget.css',
                    [],
                    '1.0.0'
                );
            }
        }
    }

    public static function render($atts): string
    {
        $atts  = shortcode_atts(['id' => 0], $atts, 'ap_widget');
        $post  = get_post((int) $atts['id']);

        if (!$post || $post->post_type !== 'dashboard_widget') {
            return '';
        }

        $user  = wp_get_current_user();
        $roles = get_post_meta($post->ID, 'visible_to_roles', true) ?: [];
        if ($roles && !array_intersect($user->roles, (array) $roles)) {
            return '';
        }

        ob_start();
        ?>
        <div class="ap-widget <?php echo esc_attr(get_post_meta($post->ID, 'widget_class', true)); ?>">
            <div class="ap-widget-header">
                <?php echo esc_html(get_the_title($post)); ?>
            </div>
            <div class="ap-widget-body">
                <?php echo apply_filters('the_content', $post->post_content); ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
