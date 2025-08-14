<?php
namespace ArtPulse\Support;

final class WidgetIds {
    // Canonical IDs must all be underscore + "widget_" prefixed.
    private static array $aliases = [
        // Non-prefixed / hyphenated → canonical
        'membership'               => 'widget_membership',
        'upgrade'                  => 'widget_upgrade',
        'account_tools'            => 'widget_account_tools',
        'recommended_for_you'      => 'widget_recommended_for_you',
        'my_rsvps'                 => 'widget_my_rsvps',
        'favorites'                => 'widget_favorites',
        'local_events'             => 'widget_local_events',
        'my_follows'               => 'widget_my_follows',
        'notifications'            => 'widget_notifications',
        'messages'                 => 'widget_messages',
        'dashboard_feedback'       => 'widget_dashboard_feedback',
        'cat_fact'                 => 'widget_cat_fact',
        'news'                     => 'widget_news_feed',

        // Duplicates / legacy slugs → canonical
        'widget_news'                  => 'widget_news_feed',
        'widget_widget_events'         => 'widget_events',
        'widget_widget_favorites'      => 'widget_favorites',
        'widget_widget_near_me_events' => 'widget_near_me_events',
        'widget_widget_near_me'        => 'widget_near_me_events',
        'widget_local_events'          => 'widget_local_events',
        'widget_account_tools'         => 'widget_account_tools',
    ];

    public static function canonicalize( $id ): string {
        if (!is_string($id)) {
            return '';
        }
        $id = strtolower($id);
        $id = str_replace('-', '_', $id);
        if ($id === '') {
            return '';
        }
        // Ensure "widget_" prefix for canonical space
        if (strpos($id, 'widget_') !== 0 && !isset(self::$aliases[$id])) {
            $id = 'widget_' . $id;
        }
        return self::$aliases[$id] ?? $id;
    }
}
