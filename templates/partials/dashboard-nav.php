<?php
/**
 * Dashboard navigation links.
 *
 * Expected variables: $show_notifications (bool).
 */

$current_user = wp_get_current_user();
$roles        = (array) $current_user->roles;

?>
<nav class="dashboard-nav flex flex-wrap gap-4 mb-4">
    <a href="#membership" class="flex gap-2 items-center px-3 py-2 rounded-xl hover:bg-indigo-50">
        <span class="dashicons dashicons-admin-users"></span><?php esc_html_e('Membership', 'artpulse'); ?>
    </a>

    <?php if (in_array('organization', $roles, true)) : ?>
    <a href="#next-payment" class="flex gap-2 items-center px-3 py-2 rounded-xl hover:bg-indigo-50">
        <span class="dashicons dashicons-money"></span><?php esc_html_e('Next Payment', 'artpulse'); ?>
    </a>
    <a href="#transactions" class="flex gap-2 items-center px-3 py-2 rounded-xl hover:bg-indigo-50">
        <span class="dashicons dashicons-list-view"></span><?php esc_html_e('Transactions', 'artpulse'); ?>
    </a>
    <?php endif; ?>

    <a href="#upgrade" class="flex gap-2 items-center px-3 py-2 rounded-xl hover:bg-indigo-50">
        <span class="dashicons dashicons-star-filled"></span><?php esc_html_e('Upgrade', 'artpulse'); ?>
    </a>

    <?php if (in_array('artist', $roles, true) || in_array('organization', $roles, true)) : ?>
    <a href="#content" class="flex gap-2 items-center px-3 py-2 rounded-xl hover:bg-indigo-50">
        <span class="dashicons dashicons-media-default"></span><?php esc_html_e('Content', 'artpulse'); ?>
    </a>
    <?php endif; ?>

    <a href="#local-events" class="flex gap-2 items-center px-3 py-2 rounded-xl hover:bg-indigo-50">
        <span class="dashicons dashicons-location-alt"></span><?php esc_html_e('Local Events', 'artpulse'); ?>
    </a>
    <a href="#favorites" class="flex gap-2 items-center px-3 py-2 rounded-xl hover:bg-indigo-50">
        <span class="dashicons dashicons-heart"></span><?php esc_html_e('Favorites', 'artpulse'); ?>
    </a>
    <a href="#events" class="flex gap-2 items-center px-3 py-2 rounded-xl hover:bg-indigo-50">
        <span class="dashicons dashicons-calendar"></span><?php esc_html_e('Events', 'artpulse'); ?>
    </a>
    <a href="#engagement" class="flex gap-2 items-center px-3 py-2 rounded-xl hover:bg-indigo-50">
        <span class="dashicons dashicons-format-status"></span><?php esc_html_e('Activity', 'artpulse'); ?>
    </a>
    <a href="#account-tools" class="flex gap-2 items-center px-3 py-2 rounded-xl hover:bg-indigo-50">
        <span class="dashicons dashicons-download"></span><?php esc_html_e('Account', 'artpulse'); ?>
    </a>
    <?php if (!empty($show_notifications)) : ?>
    <a href="#notifications" class="flex gap-2 items-center px-3 py-2 rounded-xl hover:bg-indigo-50">
        <span class="dashicons dashicons-megaphone"></span><?php esc_html_e('Notifications', 'artpulse'); ?>
    </a>
    <?php endif; ?>
</nav>
