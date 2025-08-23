<?php
namespace ArtPulse\Admin;

class ShortcodePages
{
    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'addMenu']);
    }

    public static function addMenu(): void
    {
        add_submenu_page(
            'artpulse-settings',
            __('Shortcode Pages', 'artpulse'),
            __('Shortcode Pages', 'artpulse'),
            'manage_options',
            'artpulse-shortcode-pages',
            [self::class, 'render']
        );
    }

    private static function get_page_ids(): array
    {
        return array_map('intval', (array) get_option('ap_shortcode_page_ids', []));
    }

    /**
     * Return the mapping of shortcode tags to page titles.
     *
     * Keeping this list public makes it easier for tests and
     * integrations to verify all available shortcodes.
     */
    public static function get_shortcode_map(): array
    {
        // The list of shortcodes now derives from the central registry so new
        // shortcodes automatically appear in generated docs and tests.
        return \ArtPulse\Core\ShortcodeRegistry::all();
    }

    public static function create_pages(array $selected = []): void
    {
        $map = self::get_shortcode_map();
        if (!empty($selected)) {
            $map = array_intersect_key($map, array_flip($selected));
        }

        $created = self::get_page_ids();
        global $wpdb;
        foreach ($map as $shortcode => $title) {
            $tag      = trim($shortcode, '[]');
            $like     = ap_db_like('[' . $tag);
            $existing = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status != 'trash' AND post_content LIKE %s",
                    $like
                )
            );
            if (!empty($existing)) {
                $created = array_merge($created, array_map('intval', $existing));
                continue;
            }
            $args = [
                'post_title'   => $title,
                'post_content' => $shortcode,
                'post_status'  => 'publish',
                'post_type'    => 'page',
            ];
            if ($shortcode === '[user_dashboard]') {
                $args['post_name'] = 'dashboard';
            }
            $page_id = wp_insert_post($args);
            if ($page_id && !is_wp_error($page_id)) {
                $created[] = $page_id;
            }
        }
        if (!empty($created)) {
            update_option('ap_shortcode_page_ids', array_values(array_unique($created)));
        }
    }

    public static function toggle_pages(bool $disable): void
    {
        $page_ids = self::get_page_ids();
        if (empty($page_ids)) {
            return;
        }
        $status = $disable ? 'draft' : 'publish';
        foreach ($page_ids as $id) {
            wp_update_post([
                'ID'          => $id,
                'post_status' => $status,
            ]);
        }
    }

    public static function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'artpulse'));
        }

        if (isset($_POST['ap_create_shortcode_pages']) && check_admin_referer('ap_create_shortcode_pages')) {
            $selected = array_map('sanitize_text_field', $_POST['ap_shortcodes'] ?? []);
            self::create_pages($selected);
            echo '<div class="notice notice-success"><p>' . esc_html__('Pages created.', 'artpulse') . '</p></div>';
        }

        if (isset($_POST['ap_enable_shortcode_pages']) && check_admin_referer('ap_toggle_shortcode_pages')) {
            self::toggle_pages(false);
            echo '<div class="notice notice-success"><p>' . esc_html__('Pages enabled.', 'artpulse') . '</p></div>';
        }

        if (isset($_POST['ap_disable_shortcode_pages']) && check_admin_referer('ap_toggle_shortcode_pages')) {
            self::toggle_pages(true);
            echo '<div class="notice notice-success"><p>' . esc_html__('Pages disabled.', 'artpulse') . '</p></div>';
        }
        $page_map = [];
        foreach (self::get_page_ids() as $id) {
            $content = trim((string) get_post_field('post_content', $id));
            if ($content !== '') {
                $page_map[$content] = [
                    'id'   => $id,
                    'link' => get_permalink($id),
                ];
            }
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Shortcode Pages', 'artpulse'); ?></h1>
            <form method="post">
                <?php wp_nonce_field('ap_create_shortcode_pages'); ?>
                <p>
                    <label for="ap-shortcode-search" class="screen-reader-text"><?php esc_html_e('Search Shortcodes', 'artpulse'); ?></label>
                    <input type="search" id="ap-shortcode-search" placeholder="<?php esc_attr_e('Search shortcodes', 'artpulse'); ?>" style="max-width:300px;">
                </p>
                <fieldset id="ap-shortcode-list">
                    <legend class="screen-reader-text"><?php esc_html_e('Select Shortcodes', 'artpulse'); ?></legend>
                    <?php foreach (self::get_shortcode_map() as $code => $label) :
                        $exists = isset($page_map[$code]);
                        $search = strtolower($label . ' ' . $code);
                        ?>
                        <label class="ap-shortcode-option" data-search="<?php echo esc_attr($search); ?>">
                            <input type="checkbox" name="ap_shortcodes[]" value="<?php echo esc_attr($code); ?>" <?php checked(!$exists); ?> <?php disabled($exists); ?>>
                            <?php echo esc_html($label . ' ' . $code); ?>
                            <?php if ($exists) : ?>
                                <span class="ap-shortcode-created">
                                    <?php
                                    printf(
                                        '<a href="%s" target="_blank">%s</a>',
                                        esc_url($page_map[$code]['link']),
                                        esc_html__('View', 'artpulse')
                                    );
                                    ?>
                                </span>
                            <?php endif; ?>
                        </label>
                    <?php endforeach; ?>
                </fieldset>
                <input type="submit" name="ap_create_shortcode_pages" class="button button-primary" value="<?php esc_attr_e('Create Pages', 'artpulse'); ?>">
            </form>
            <form method="post">
                <?php wp_nonce_field('ap_toggle_shortcode_pages'); ?>
                <input type="submit" name="ap_enable_shortcode_pages" class="button button-secondary" value="<?php esc_attr_e('Enable Pages', 'artpulse'); ?>">
                <input type="submit" name="ap_disable_shortcode_pages" class="button button-secondary" value="<?php esc_attr_e('Disable Pages', 'artpulse'); ?>">
            </form>
        </div>
        <script>
            document.getElementById('ap-shortcode-search').addEventListener('input', function () {
                var term = this.value.toLowerCase();
                document.querySelectorAll('#ap-shortcode-list .ap-shortcode-option').forEach(function (el) {
                    el.style.display = el.dataset.search.indexOf(term) === -1 ? 'none' : '';
                });
            });
        </script>
        <?php
    }
}
