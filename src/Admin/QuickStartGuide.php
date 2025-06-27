<?php
namespace ArtPulse\Admin;

class QuickStartGuide
{
    const DISMISS_META = 'ap_dismiss_quickstart';

    public static function register()
    {
        add_action('admin_menu', [self::class, 'addMenu']);
        add_action('admin_notices', [self::class, 'welcomeNotice']);
        add_action('admin_init', [self::class, 'handleDismiss']);
    }

    public static function addMenu()
    {
        add_submenu_page(
            'artpulse-settings',
            __('Quick Start', 'artpulse'),
            __('Quick Start', 'artpulse'),
            'manage_options',
            'artpulse-quickstart',
            [self::class, 'render']
        );
    }

    public static function render()
    {
        $admin_doc  = plugins_url('assets/docs/Admin_Help.md', ARTPULSE_PLUGIN_FILE);
        $member_doc = plugins_url('assets/docs/Member_Help.md', ARTPULSE_PLUGIN_FILE);
        // Images were previously served from the plugin but removed to keep the repository lightweight.
        // Use remote placeholders to demonstrate modal viewing without bundling binaries.
        $img_base   = 'https://via.placeholder.com/300?text=';

        echo '<div class="wrap ap-dashboard">';
        echo '<h1>' . esc_html__('ArtPulse Quick Start', 'artpulse') . '</h1>';

        echo '<section class="ap-widget" id="ap-qs-admin">';
        echo '<h2>' . esc_html__('Admin Guide', 'artpulse') . '</h2>';
        echo '<ol class="ap-qs-steps">';
        echo '<li>' . esc_html__('Install and configure settings', 'artpulse') . '</li>';
        echo '<li>' . esc_html__('Import or export data via CSV', 'artpulse') . '</li>';
        echo '<li>' . esc_html__('Manage members and events', 'artpulse') . '</li>';
        echo '</ol>';
        echo '<div class="mermaid">graph TD;A[Start Signup]-->B[Create Profile];B-->C[Register Artwork];C-->D[Submit Event];D-->E[View Calendar];E-->F[Upgrade Membership];F-->G[Receive Notifications];</div>';
        echo '<p><a href="' . esc_url($admin_doc) . '" target="_blank" class="ap-form-button nectar-button">' . esc_html__('Open Full Guide', 'artpulse') . '</a></p>';
        echo '<div class="ap-qs-thumbs">';
        echo '<img class="qs-thumb" data-img="' . esc_url($img_base . 'admin_modules_walkthrough.png') . '" src="' . esc_url($img_base . 'admin_modules_walkthrough.png') . '" alt="" />';
        echo '</div>';
        echo '</section>';

        echo '<section class="ap-widget" id="ap-qs-member">';
        echo '<h2>' . esc_html__('Member Guide', 'artpulse') . '</h2>';
        echo '<ol class="ap-qs-steps">';
        echo '<li>' . esc_html__('Create your profile and register artworks', 'artpulse') . '</li>';
        echo '<li>' . esc_html__('Submit events for promotion', 'artpulse') . '</li>';
        echo '<li>' . esc_html__('Browse the calendar and upgrade when ready', 'artpulse') . '</li>';
        echo '</ol>';
        echo '<p><a href="' . esc_url($member_doc) . '" target="_blank" class="ap-form-button nectar-button">' . esc_html__('Open Full Guide', 'artpulse') . '</a></p>';
        echo '<div class="ap-qs-thumbs">';
        echo '<img class="qs-thumb" data-img="' . esc_url($img_base . 'event_calendar_view.png') . '" src="' . esc_url($img_base . 'event_calendar_view.png') . '" alt="" />';
        echo '<img class="qs-thumb" data-img="' . esc_url($img_base . 'artwork_registration_form.png') . '" src="' . esc_url($img_base . 'artwork_registration_form.png') . '" alt="" />';
        echo '<img class="qs-thumb" data-img="' . esc_url($img_base . 'event_registration_form.png') . '" src="' . esc_url($img_base . 'event_registration_form.png') . '" alt="" />';
        echo '</div>';
        echo '</section>';

        echo '<div id="ap-qs-modal" class="ap-org-modal">';
        echo '<button id="ap-qs-modal-close" type="button" class="ap-form-button nectar-button">' . esc_html__('Close', 'artpulse') . '</button>';
        echo '<img id="ap-qs-modal-img" alt="" />';
        echo '</div>';

        echo '</div>';
    }

    public static function welcomeNotice()
    {
        if (get_user_meta(get_current_user_id(), self::DISMISS_META, true)) {
            return;
        }
        $link = admin_url('admin.php?page=artpulse-quickstart');
        $dismiss_url = add_query_arg('ap_dismiss_quickstart', '1');
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p>' . sprintf(esc_html__('Welcome to ArtPulse! Visit the %sQuick Start Guide%s.', 'artpulse'), '<a href="' . esc_url($link) . '">', '</a>') . ' <a href="' . esc_url($dismiss_url) . '">' . esc_html__('Dismiss', 'artpulse') . '</a></p>';
        echo '</div>';
    }

    public static function handleDismiss()
    {
        if (isset($_GET['ap_dismiss_quickstart'])) {
            update_user_meta(get_current_user_id(), self::DISMISS_META, 1);
            wp_safe_redirect(remove_query_arg('ap_dismiss_quickstart'));
            exit;
        }
    }
}
