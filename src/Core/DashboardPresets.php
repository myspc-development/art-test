<?php
namespace ArtPulse\Core;

use ArtPulse\Support\WidgetIds;

class DashboardPresets {

	/** @var string[] */
	private const ROLES = array( 'member', 'artist', 'organization' );

	/**
	 * Cache of role → widget slugs.
	 *
	 * @var array<string, array<int, string>>
	 */
	private static array $cache = array();

	/** Clear the cached presets. */
	public static function resetCache(): void {
		self::$cache = array();
	}

	/**
	 * Return the canonical list of widget slugs for a role.
	 * Looks for JSON under current and legacy paths; falls back to hard-coded defaults.
	 *
	 * @param string $role
	 * @return array<int,string>
	 */
	public static function forRole( string $role ): array {
                $role = \sanitize_key( $role );
		if ( ! in_array( $role, self::ROLES, true ) ) {
			$role = 'member';
		}

		if ( isset( self::$cache[ $role ] ) ) {
			return self::$cache[ $role ];
		}

               // Resolve plugin root using WordPress helper to support symlinks.
               // plugin_dir_path( __DIR__ ) yields the `src/` directory so step up one level.
               $root = \plugin_dir_path( __DIR__ ) . '../';

               $current    = "$root/data/preset-$role.json";
               $candidates = array();

               // Only look at legacy presets if the current file is readable. Otherwise,
               // skip them entirely and fall back to the built-in defaults.
               if ( @is_readable( $current ) ) {
                       $candidates = array(
                               $current,
                               "$root/data/presets/{$role}-default.json",
                               "$root/data/presets/$role.json",
                               // organization sometimes used compact/admin variants historically
                               $role === 'organization' ? "$root/data/presets/organization-compact.json" : null,
                               $role === 'organization' ? "$root/data/presets/organization-admin.json" : null,
                               // earlier member/artist variants
                               $role === 'member' ? "$root/data/presets/member-discovery.json" : null,
                               $role === 'artist' ? "$root/data/presets/artist-default.json" : null,
                               $role === 'artist' ? "$root/data/presets/artist-tools.json" : null,
                       );
                       $candidates = array_values( array_filter( $candidates, 'is_string' ) );
               }

                // Hard-coded defaults used when no valid JSON exists.
                $defaults = array(
                        'member'       => array(
                                'widget_membership',
                                'widget_account_tools',
                                'widget_my_follows',
                                'widget_recommended_for_you',
                                'widget_local_events',
                                'widget_my_events',
                                'widget_site_stats',
                        ),
                        'artist'       => array(
                                'widget_artist_revenue_summary',
                                'widget_artist_artwork_manager',
                                'widget_artist_audience_insights',
                                'widget_artist_feed_publisher',
                                'widget_my_events',
                                'widget_site_stats',
                        ),
                        'organization' => array(
                                'widget_audience_crm',
                                'widget_org_ticket_insights',
                                'widget_webhooks',
                                'widget_my_events',
                                'widget_site_stats',
                        ),
                );

                $expected = count( $defaults[ $role ] );
                $slugs    = array();

                foreach ( $candidates as $file ) {
                        if ( ! @is_readable( $file ) ) {
                                continue; // Try next candidate if file cannot be read.
                        }
                        $raw = @file_get_contents( $file );
                        if ( ! is_string( $raw ) || $raw === '' ) {
                                continue;
                        }
                        $json = json_decode( $raw, true );
                        if ( ! is_array( $json ) ) {
                                continue;
                        }
                        $list = isset( $json['widgets'] ) && is_array( $json['widgets'] )
                                ? $json['widgets']
                                : ( array_keys( $json ) === range( 0, count( $json ) - 1 ) ? $json : array() );
                        $tmp = array();
                        foreach ( $list as $item ) {
                                $slug = null;
                                if ( is_array( $item ) ) {
                                        if ( isset( $item['id'] ) ) {
                                                $slug = $item['id'];
                                        } elseif ( isset( $item['slug'] ) ) {
                                                $slug = $item['slug'];
                                        }
                                } else {
                                        $slug = $item;
                                }

                                if ( is_string( $slug ) && $slug !== '' ) {
                                        $canon = WidgetIds::canonicalize( $slug );
                                        if ( $canon && ! in_array( $canon, $tmp, true ) ) {
                                                $tmp[] = $canon;
                                        }
                                }
                        }

                        if ( count( $tmp ) >= $expected ) {
                                $slugs = $tmp;
                                break; // Use the first candidate with a full list.
                        }
                }

                if ( empty( $slugs ) ) {
                        $slugs = $defaults[ $role ];
                }

                self::$cache[ $role ] = $slugs;

                return $slugs;
        }
}
