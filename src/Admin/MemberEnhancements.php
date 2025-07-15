<?php

// Enhancements Part 1: Admin Notes, Badges, Heatmap
namespace ArtPulse\Admin;

class MemberEnhancements
{
    public static function register()
    {
        add_action('show_user_profile', [self::class, 'renderNotesField']);
        add_action('edit_user_profile', [self::class, 'renderNotesField']);
        add_action('personal_options_update', [self::class, 'saveNotes']);
        add_action('edit_user_profile_update', [self::class, 'saveNotes']);

        add_action('show_user_profile', [self::class, 'renderBadgesField']);
        add_action('edit_user_profile', [self::class, 'renderBadgesField']);
        add_action('personal_options_update', [self::class, 'saveBadges']);
        add_action('edit_user_profile_update', [self::class, 'saveBadges']);

        add_action('admin_menu', [self::class, 'addHeatmapMenu']);
    }

    // 1. Admin-only Notes field
    public static function renderNotesField($user)
    {
        if (!current_user_can('manage_options')) return;

        $note = get_user_meta($user->ID, 'ap_admin_note', true);

        echo '<h2 class="ap-card__title">Admin Notes</h2>';
        echo '<textarea name="ap_admin_note" rows="5" cols="70">' . esc_textarea($note) . '</textarea>';
        echo '<p class="description">Visible only to site administrators.</p>';
    }

    public static function saveNotes($user_id)
    {
        if (!current_user_can('manage_options')) return;

        update_user_meta($user_id, 'ap_admin_note', sanitize_textarea_field($_POST['ap_admin_note'] ?? ''));
    }

    // 2. Badge management
    public static function renderBadgesField($user)
    {
        if (!current_user_can('manage_options')) return;

        $badges = get_user_meta($user->ID, 'user_badges', true);
        if (!is_array($badges)) {
            $badges = [];
        }

        echo '<h2 class="ap-card__title">' . esc_html__('User Badges', 'artpulse') . '</h2>';
        echo '<input type="text" name="user_badges" value="' . esc_attr(implode(',', $badges)) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__('Comma-separated badge slugs.', 'artpulse') . '</p>';
    }

    public static function saveBadges($user_id)
    {
        if (!current_user_can('manage_options')) return;

        $input = sanitize_text_field($_POST['user_badges'] ?? '');
        $badges = array_filter(array_map('trim', explode(',', $input)));
        update_user_meta($user_id, 'user_badges', $badges);
    }

    // 3. Login heatmap page
    public static function addHeatmapMenu(): void
    {
        add_submenu_page(
            'artpulse-settings',
            __('Login Heatmap', 'artpulse'),
            __('Login Heatmap', 'artpulse'),
            'manage_options',
            'ap-login-heatmap',
            [self::class, 'renderHeatmapPage']
        );
    }

    public static function renderHeatmapPage(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_login_events';
        $since = date('Y-m-d H:i:s', strtotime('-7 days'));
        $events = $wpdb->get_results($wpdb->prepare("SELECT login_at FROM $table WHERE login_at >= %s", $since));

        $data = array_fill(0, 7, array_fill(0, 24, 0));
        foreach ($events as $event) {
            $ts = strtotime($event->login_at);
            $day = (int) date('w', $ts);
            $hour = (int) date('G', $ts);
            $data[$day][$hour]++;
        }

        $max = 0;
        foreach ($data as $row) {
            $m = max($row);
            if ($m > $max) {
                $max = $m;
            }
        }

        $days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Login Heatmap', 'artpulse') . '</h1>';
        echo '<table class="widefat fixed striped">';
        echo '<thead><tr><th>' . esc_html__('Day/Hour', 'artpulse') . '</th>';
        for ($h = 0; $h < 24; $h++) {
            echo '<th>' . esc_html($h) . '</th>';
        }
        echo '</tr></thead><tbody>';
        for ($d = 0; $d < 7; $d++) {
            echo '<tr><td>' . esc_html($days[$d]) . '</td>';
            for ($h = 0; $h < 24; $h++) {
                $count = $data[$d][$h];
                $opacity = $max ? ($count / $max) : 0;
                $style = 'background-color: rgba(0,123,255,' . $opacity . ')';
                echo '<td>' . ($count ? $count : '') . '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table></div>';
    }
}
