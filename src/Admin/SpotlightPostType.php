<?php
namespace ArtPulse\Admin;

/**
 * Register the "spotlight" custom post type and meta boxes.
 */
class SpotlightPostType
{
    public static function register(): void
    {
        add_action('init', [self::class, 'register_cpt']);
        add_action('add_meta_boxes', [self::class, 'add_meta_boxes']);
        add_action('save_post_spotlight', [self::class, 'save_meta']);
    }

    public static function register_cpt(): void
    {
        register_post_type('spotlight', [
            'label'        => __('Spotlights', 'artpulse'),
            'public'       => false,
            'show_ui'      => true,
            'show_in_rest' => true,
            'supports'     => ['title', 'editor'],
            'menu_icon'    => 'dashicons-star-filled',
        ]);

        register_post_meta('spotlight', 'visible_to_roles', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'array',
        ]);
        register_post_meta('spotlight', 'start_at', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);
        register_post_meta('spotlight', 'expires_at', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);
        register_post_meta('spotlight', 'cta_text', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);
        register_post_meta('spotlight', 'cta_url', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);
        register_post_meta('spotlight', 'cta_target', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);
    }

    public static function add_meta_boxes(): void
    {
        add_meta_box('spotlight_roles', __('Visible To Roles', 'artpulse'), [self::class, 'render_roles_meta'], 'spotlight', 'side');
        add_meta_box('spotlight_schedule', __('Visibility Schedule', 'artpulse'), [self::class, 'render_schedule_meta'], 'spotlight', 'side');
        add_meta_box('spotlight_cta', __('Call to Action', 'artpulse'), [self::class, 'render_cta_meta'], 'spotlight', 'normal');
    }

    public static function render_roles_meta($post): void
    {
        $roles    = ['member', 'artist', 'organization'];
        $selected = get_post_meta($post->ID, 'visible_to_roles', true) ?: [];
        foreach ($roles as $role) {
            $checked = in_array($role, (array) $selected, true) ? 'checked' : '';
            echo "<label><input type='checkbox' name='visible_to_roles[]' value='{$role}' {$checked} /> " . esc_html($role) . "</label><br>";
        }
        // Scheduling is handled in a separate meta box.
    }

    public static function render_schedule_meta($post): void
    {
        $start = get_post_meta($post->ID, 'start_at', true);
        $end   = get_post_meta($post->ID, 'expires_at', true);
        ?>
        <p><label><?php _e('Start Date', 'artpulse'); ?><br>
            <input type="date" name="start_at" value="<?= esc_attr($start) ?>" class="widefat" /></label></p>

        <p><label><?php _e('End Date', 'artpulse'); ?><br>
            <input type="date" name="expires_at" value="<?= esc_attr($end) ?>" class="widefat" /></label></p>
        <?php
    }

    public static function render_cta_meta($post): void
    {
        $text   = get_post_meta($post->ID, 'cta_text', true);
        $url    = get_post_meta($post->ID, 'cta_url', true);
        $target = get_post_meta($post->ID, 'cta_target', true);
        ?>
        <p><label><?php _e('Button Text', 'artpulse'); ?><br>
            <input type="text" name="cta_text" value="<?= esc_attr($text) ?>" class="widefat" /></label></p>
        <p><label><?php _e('URL', 'artpulse'); ?><br>
            <input type="url" name="cta_url" value="<?= esc_url($url) ?>" class="widefat" /></label></p>
        <p><label><input type="checkbox" name="cta_target" value="_blank" <?= $target === '_blank' ? 'checked' : '' ?> />
            <?php _e('Open in new tab', 'artpulse'); ?></label></p>
        <?php
    }

    public static function save_meta(int $post_id): void
    {
        if (isset($_POST['visible_to_roles'])) {
            update_post_meta($post_id, 'visible_to_roles', array_map('sanitize_text_field', (array) $_POST['visible_to_roles']));
        } else {
            delete_post_meta($post_id, 'visible_to_roles');
        }

        if (isset($_POST['start_at'])) {
            update_post_meta($post_id, 'start_at', sanitize_text_field($_POST['start_at']));
        } else {
            delete_post_meta($post_id, 'start_at');
        }

        if (isset($_POST['expires_at'])) {
            update_post_meta($post_id, 'expires_at', sanitize_text_field($_POST['expires_at']));
        } else {
            delete_post_meta($post_id, 'expires_at');
        }

        update_post_meta($post_id, 'cta_text', sanitize_text_field($_POST['cta_text'] ?? ''));
        update_post_meta($post_id, 'cta_url', esc_url_raw($_POST['cta_url'] ?? ''));
        update_post_meta($post_id, 'cta_target', sanitize_text_field($_POST['cta_target'] ?? ''));
    }
}
