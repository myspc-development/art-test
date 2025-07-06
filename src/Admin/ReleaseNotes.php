<?php
namespace ArtPulse\Admin;

use ArtPulse\Core\Plugin;

class ReleaseNotes
{
    const OPTION = 'ap_plugin_version';
    const DISMISS_META = 'ap_dismiss_release_notes';

    public static function register()
    {
        add_action('admin_init', [self::class, 'check_version']);
        add_action('wp_ajax_ap_dismiss_release_notes', [self::class, 'dismiss']);
    }

    public static function check_version()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $current = defined('ARTPULSE_VERSION') ? ARTPULSE_VERSION : Plugin::VERSION;
        $stored  = get_option(self::OPTION);

        if ($stored !== $current) {
            update_option(self::OPTION, $current);
        }

        if (get_user_meta(get_current_user_id(), self::DISMISS_META, true) === $current) {
            return;
        }

        add_action('admin_enqueue_scripts', [self::class, 'enqueue']);
        add_action('admin_footer', [self::class, 'render_modal']);
    }

    public static function enqueue()
    {
        wp_enqueue_style(
            'ap-style',
            plugins_url('assets/css/ap-style.css', ARTPULSE_PLUGIN_FILE)
        );
        wp_enqueue_script(
            'ap-release-notes',
            plugins_url('assets/js/ap-release-notes.js', ARTPULSE_PLUGIN_FILE),
            ['jquery'],
            '1.0.0',
            true
        );
        wp_localize_script('ap-release-notes', 'APReleaseNotes', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('ap_release_notes'),
        ]);
    }

    public static function render_modal()
    {
        echo '<div id="ap-release-notes-modal" class="ap-org-modal">';
        echo '<button id="ap-release-close" type="button" class="ap-form-button nectar-button">' . esc_html__('Close', 'artpulse') . '</button>';
        echo '<div class="ap-release-content">' . wp_kses_post(self::get_notes()) . '</div>';
        echo '</div>';
    }

    public static function dismiss()
    {
        check_ajax_referer('ap_release_notes', 'nonce');
        update_user_meta(get_current_user_id(), self::DISMISS_META, ARTPULSE_VERSION);
        wp_send_json_success();
    }

    private static function get_notes(): string
    {
        $file = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'docs/CHANGELOG.md';
        if (!file_exists($file)) {
            return '';
        }
        $content  = file_get_contents($file);
        $parts = preg_split('/^##\s+/m', $content);
        if (isset($parts[1])) {
            $section = trim(strtok($parts[1], '#'));
            return wpautop($section);
        }
        return '';
    }
}
