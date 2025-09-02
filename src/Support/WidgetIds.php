<?php
namespace ArtPulse\Support;

final class WidgetIds {
	// Canonical IDs must all be underscore + "widget_" prefixed.
	private static array $aliases = array(
		// Non-prefixed / hyphenated → canonical
		'membership'                   => 'widget_membership',
		'upgrade'                      => 'widget_upgrade',
		'account_tools'                => 'widget_account_tools',
		'recommended_for_you'          => 'widget_recommended_for_you',
		'my_rsvps'                     => 'widget_my_rsvps',
		'favorites'                    => 'widget_favorites',
		'local_events'                 => 'widget_local_events',
		'upcoming_events_by_location'  => 'widget_local_events',
                'my_events'                    => 'widget_my_events',
                'myevents'                     => 'widget_my_events',
		'site_stats'                   => 'widget_site_stats',
		'my_follows'                   => 'widget_my_follows',
		'notifications'                => 'widget_notifications',
		'messages'                     => 'widget_messages',
		'dashboard_feedback'           => 'widget_dashboard_feedback',
		'cat_fact'                     => 'widget_cat_fact',
                'news'                         => 'widget_news',

		// Organization widgets
		'lead_capture'                 => 'widget_audience_crm',
		'audience_crm'                 => 'widget_audience_crm',
		'rsvp_stats'                   => 'widget_org_ticket_insights',
		'org_ticket_insights'          => 'widget_org_ticket_insights',
		'webhooks'                     => 'widget_webhooks',

		// Duplicates / legacy slugs → canonical
                'widget_news_feed'             => 'widget_news',
		'widget_widget_events'         => 'widget_events',
		'widget_widget_favorites'      => 'widget_favorites',
		'widget_widget_near_me_events' => 'widget_near_me_events',
		'widget_widget_near_me'        => 'widget_near_me_events',
		'widget_local_events'          => 'widget_local_events',
		'widget_account_tools'         => 'widget_account_tools',
		'widget_followed_artists'      => 'widget_my_follows',
		'followed_artists'             => 'widget_my_follows',
	);

	public static function canonicalize( $id ): string {
		if ( ! is_string( $id ) ) {
			return '';
		}
                $id = strtolower( $id );
                $id = str_replace( '-', '_', $id );
                $id = preg_replace( '/[^a-z0-9_]/', '', $id );
                $id = trim( $id, '_' );
                if ( $id === '' ) {
                        return '';
                }
                // Ensure "widget_" prefix for canonical space
                if ( strpos( $id, 'widget_' ) !== 0 && ! isset( self::$aliases[ $id ] ) ) {
                        $id = 'widget_' . $id;
                }
                return self::$aliases[ $id ] ?? $id;
        }
}
