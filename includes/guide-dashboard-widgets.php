<?php
/**
 * Dashboard widgets that display the Admin and Member guides.
 */

if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\Core\DashboardWidgetRegistry;

function ap_widget_admin_guide(int $user_id = 0, array $vars = []): string
{
    $doc = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'assets/docs/Admin_Help.md';
    $parsedown = new Parsedown();
    if (method_exists($parsedown, 'setSafeMode')) {
        $parsedown->setSafeMode(true);
    } elseif (method_exists($parsedown, 'setMarkupEscaped')) {
        $parsedown->setMarkupEscaped(true);
    }
    $content = file_exists($doc) ? $parsedown->text(file_get_contents($doc)) : '<p>' . esc_html__('Guide not found.', 'artpulse') . '</p>';
    $vars['id'] = 'admin-guide';
    $vars['title'] = __('Admin Guide', 'artpulse');
    $vars['content'] = $content;
    return ap_load_dashboard_template('widgets/admin-guide.php', $vars);
}

function ap_widget_member_guide(int $user_id = 0, array $vars = []): string
{
    $doc = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'assets/docs/Member_Help.md';
    $parsedown = new Parsedown();
    if (method_exists($parsedown, 'setSafeMode')) {
        $parsedown->setSafeMode(true);
    } elseif (method_exists($parsedown, 'setMarkupEscaped')) {
        $parsedown->setMarkupEscaped(true);
    }
    $content = file_exists($doc) ? $parsedown->text(file_get_contents($doc)) : '<p>' . esc_html__('Guide not found.', 'artpulse') . '</p>';
    $vars['id'] = 'member-guide';
    $vars['title'] = __('Member Guide', 'artpulse');
    $vars['content'] = $content;
    return ap_load_dashboard_template('widgets/member-guide.php', $vars);
}

function ap_register_guide_widgets(): void
{
    DashboardWidgetRegistry::register(
        'admin_guide',
        'Admin Guide',
        'book-open',
        'Getting started instructions for administrators.',
        'ap_widget_admin_guide',
        [ 'roles' => ['administrator', 'organization'] ]
    );

    DashboardWidgetRegistry::register(
        'member_guide',
        'Member Guide',
        'book',
        'Getting started instructions for members.',
        'ap_widget_member_guide',
        [ 'roles' => ['member', 'artist', 'organization'] ]
    );
}
add_action('artpulse_register_dashboard_widget', 'ap_register_guide_widgets');
