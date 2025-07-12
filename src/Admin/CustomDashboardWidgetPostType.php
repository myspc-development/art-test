<?php
namespace ArtPulse\Admin;

class CustomDashboardWidgetPostType
{
    public static function register(): void
    {
        add_action('init', [self::class, 'register_cpt']);
        add_action('add_meta_boxes', [self::class, 'add_meta_boxes']);
        add_action('save_post_dashboard_widget', [self::class, 'save_meta']);
    }

    public static function register_cpt(): void
    {
        register_post_type('dashboard_widget', [
            'label'        => __('Dashboard Widgets', 'artpulse'),
            'public'       => false,
            'show_ui'      => true,
            'menu_icon'    => 'dashicons-screenoptions',
            'supports'     => ['title', 'editor'],
            'show_in_rest' => true,
            'menu_position'=> 25,
        ]);
    }

    public static function add_meta_boxes(): void
    {
        add_meta_box('widget_options', __('Widget Options', 'artpulse'), [self::class, 'render_meta_box'], 'dashboard_widget', 'normal');
    }

    public static function render_meta_box($post): void
    {
        $roles     = ['artist', 'organization', 'member'];
        $current   = get_post_meta($post->ID, 'visible_to_roles', true) ?: [];
        $icon      = get_post_meta($post->ID, 'widget_icon', true);
        $order     = get_post_meta($post->ID, 'widget_order', true);
        $css_class = get_post_meta($post->ID, 'widget_class', true);
        ?>
        <p><label><?php _e('Roles', 'artpulse'); ?></label><br>
        <?php foreach ($roles as $role): ?>
            <label><input type="checkbox" name="visible_to_roles[]" value="<?= esc_attr($role) ?>" <?= in_array($role, (array) $current, true) ? 'checked' : '' ?>> <?= esc_html(ucfirst($role)) ?></label><br>
        <?php endforeach; ?>
        </p>
        <p><label><?php _e('Icon', 'artpulse'); ?> <input type="text" name="widget_icon" value="<?= esc_attr($icon) ?>" class="regular-text"></label></p>
        <p><label><?php _e('Order', 'artpulse'); ?> <input type="number" name="widget_order" value="<?= esc_attr($order ?: 1) ?>" class="small-text"></label></p>
        <p><label><?php _e('CSS Class', 'artpulse'); ?> <input type="text" name="widget_class" value="<?= esc_attr($css_class) ?>" class="regular-text"></label></p>
        <?php
    }

    public static function save_meta(int $post_id): void
    {
        update_post_meta($post_id, 'visible_to_roles', $_POST['visible_to_roles'] ?? []);
        update_post_meta($post_id, 'widget_icon', sanitize_text_field($_POST['widget_icon'] ?? ''));
        update_post_meta($post_id, 'widget_order', intval($_POST['widget_order'] ?? 1));
        update_post_meta($post_id, 'widget_class', sanitize_text_field($_POST['widget_class'] ?? ''));
    }
}
