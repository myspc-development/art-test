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
		$role = sanitize_key( $role );
		if ( ! in_array( $role, self::ROLES, true ) ) {
			$role = 'member';
		}

		if ( isset( self::$cache[ $role ] ) ) {
			return self::$cache[ $role ];
		}

               // Resolve plugin root using WordPress helper to support symlinks.
               // plugin_dir_path( __DIR__ ) yields the `src/` directory so step up one level.
               $root = plugin_dir_path( __DIR__ ) . '../';

		// Try current filename first, then legacy candidates
		$candidates = array(
			"$root/data/preset-$role.json",
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

               $slugs = array();
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
                                       $slugs[] = WidgetIds::canonicalize( $slug );
                               }
                       }
                       if ( $slugs ) {
                               break;
                       }
               }

               // Fallback to placeholder widgets so layout renders safely.
               if ( ! $slugs ) {
                       $fallback = array(
                               'member'       => array_fill( 0, 7, 'widget_placeholder' ),
                               'artist'       => array_fill( 0, 6, 'widget_placeholder' ),
                               'organization' => array_fill( 0, 5, 'widget_placeholder' ),
                       );
                       $slugs    = $fallback[ $role ];
               }

               // De-dupe preserving order, unless placeholders are in use.
               if ( $slugs && $slugs[0] !== 'widget_placeholder' ) {
                       $slugs = array_values( array_unique( $slugs ) );
               }

               return self::$cache[ $role ] = $slugs;
       }
}
