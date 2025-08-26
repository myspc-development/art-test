<?php
/**
 * Template Name: Simple Dashboard
 *
 * Minimal template rendering widgets for the current user's role.
 */

use ArtPulse\Core\DashboardPresets;
use ArtPulse\Core\WidgetRegistry;

if (!is_user_logged_in()) {
    return;
}

$role        = function_exists('ap_get_effective_role') ? ap_get_effective_role() : 'member';
$user_id     = get_current_user_id();
$slugs       = DashboardPresets::get_preset_for_role($role);
$validSlugs  = WidgetRegistry::list();
$slugs       = array_values(array_intersect($slugs, $validSlugs));
$context     = ['user_id' => $user_id];

echo '<div class="ap-dashboard-fallback" data-role="' . esc_attr($role) . '">';
foreach ($slugs as $slug) {
    echo WidgetRegistry::render($slug, $context);
}
echo '</div>';

