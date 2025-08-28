<?php
namespace ArtPulse\Search;

use WP_Post;
use WP_Query;

use ArtPulse\Traits\Registerable;

class ExternalSearch {
	use Registerable;

	private const HOOKS = array(
		array( 'save_post_artpulse_artist', 'sync_post', 10, 1 ),
		array( 'save_post_artpulse_org', 'sync_post', 10, 1 ),
		array( 'delete_post', 'delete_post', 10, 1 ),
	);

	public static function is_enabled(): bool {
		$service = self::get_service();
		return in_array( $service, array( 'algolia', 'elasticpress' ), true );
	}

	private static function get_service(): string {
		$opts = get_option( 'artpulse_settings', array() );
		return $opts['search_service'] ?? '';
	}

	public static function sync_post( int $post_id ): void {
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
		$service = self::get_service();
		if ( $service === 'algolia' ) {
			do_action( 'algolia_reindex_post', $post_id );
		} elseif ( $service === 'elasticpress' && function_exists( 'ep_sync_post' ) ) {
			ep_sync_post( $post_id );
		}
	}

	public static function delete_post( int $post_id ): void {
		$service = self::get_service();
		if ( $service === 'algolia' ) {
			do_action( 'algolia_delete_post', $post_id );
		} elseif ( $service === 'elasticpress' && function_exists( 'ep_delete_post' ) ) {
			ep_delete_post( $post_id );
		}
	}

	/**
	 * Search indexed content.
	 *
	 * @param string $type  Directory type (artist or org).
	 * @param array  $args  Search args like limit or taxonomy terms.
	 * @return WP_Post[]
	 */
	public static function search( string $type, array $args ): array {
		$service = self::get_service();
		if ( $service === 'algolia' ) {
			$results = apply_filters( 'algolia_search_records', array(), $type, $args );
			return is_array( $results ) ? $results : array();
		}
		if ( $service === 'elasticpress' && function_exists( 'ep_search' ) ) {
			$query = ep_search( array_merge( array( 'post_type' => 'artpulse_' . $type ), $args ) );
			return $query instanceof WP_Query ? $query->posts : array();
		}
		return array();
	}
}
