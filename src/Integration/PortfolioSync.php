<?php
namespace ArtPulse\Integration;

use ArtPulse\Core\PortfolioSyncLogger;

/**
 * Sync artist, artwork and event posts to portfolio entries.
 */
class PortfolioSync {

	/** @var bool */
	private static $syncing = false;
	/**
	 * Register hooks for syncing portfolio posts.
	 */
	public static function register() {
		$types = get_option(
			'ap_portfolio_sync_types',
			array(
				'artpulse_event',
				'artpulse_artist',
				'artpulse_org',
			)
		);
		$types = is_array( $types ) ? $types : array();
		foreach ( $types as $type ) {
			add_action( "save_post_{$type}", array( self::class, 'sync_portfolio' ), 10, 2 );
		}
		add_action( 'before_delete_post', array( self::class, 'delete_portfolio' ) );
		add_action( 'save_post_portfolio', array( self::class, 'sync_source' ), 10, 2 );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'ap sync-portfolio', array( self::class, 'cli_sync' ) );
		}
	}

	/**
	 * Create or update the portfolio entry when the source post is saved.
	 *
	 * @param int      $post_id The post ID being saved.
	 * @param \WP_Post $post    The post object.
	 */
	public static function sync_portfolio( $post_id, $post ) {
		if ( self::$syncing || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		self::$syncing = true;

		$ids = array();
		if ( in_array( $post->post_type, array( 'artpulse_artwork', 'artpulse_event', 'artpulse_org' ), true ) ) {
			$ids = get_post_meta( $post_id, '_ap_submission_images', true );
			if ( ! is_array( $ids ) ) {
				$ids = array();
			}
		}

		$existing = get_posts(
			array(
				'post_type'   => 'portfolio',
				'meta_key'    => '_ap_source_post',
				'meta_value'  => $post_id,
				'post_status' => 'any',
				'numberposts' => 1,
				'fields'      => 'ids',
			)
		);
		if ( count( $existing ) > 1 ) {
			PortfolioSyncLogger::log(
				'sync',
				'Duplicate portfolio records',
				array(
					'post' => $post_id,
					'ids'  => $existing,
				),
				get_current_user_id()
			);
		}

		if ( $existing ) {
			$portfolio_id = $existing[0];
			$result       = wp_update_post(
				array(
					'ID'           => $portfolio_id,
					'post_title'   => $post->post_title,
					'post_content' => $post->post_content,
					'post_status'  => $post->post_status,
				),
				true
			);
			if ( is_wp_error( $result ) ) {
				PortfolioSyncLogger::log(
					'sync',
					'Update failed',
					array(
						'post'  => $post_id,
						'error' => $result->get_error_message(),
					),
					get_current_user_id()
				);
			}
			update_post_meta( $post_id, '_ap_portfolio_id', $portfolio_id );
			update_post_meta( $portfolio_id, '_ap_source_type', $post->post_type );
		} else {
			$portfolio_id = wp_insert_post(
				array(
					'post_type'    => 'portfolio',
					'post_title'   => $post->post_title,
					'post_content' => $post->post_content,
					'post_status'  => $post->post_status,
					'post_author'  => $post->post_author,
				),
				true
			);
			if ( ! is_wp_error( $portfolio_id ) && $portfolio_id ) {
				update_post_meta( $portfolio_id, '_ap_source_post', $post_id );
				update_post_meta( $portfolio_id, '_ap_source_type', $post->post_type );
				update_post_meta( $post_id, '_ap_portfolio_id', $portfolio_id );
				PortfolioSyncLogger::log(
					'sync',
					'Created portfolio',
					array(
						'post'      => $post_id,
						'portfolio' => $portfolio_id,
					),
					get_current_user_id()
				);
			} else {
				PortfolioSyncLogger::log(
					'sync',
					'Insert failed',
					array(
						'post'  => $post_id,
						'error' => is_wp_error( $portfolio_id ) ? $portfolio_id->get_error_message() : 'unknown',
					),
					get_current_user_id()
				);
			}
		}

		if ( ! empty( $portfolio_id ) && ! is_wp_error( $portfolio_id ) ) {
			update_post_meta( $portfolio_id, '_ap_submission_images', $ids );

			// Map plugin content to Salient extra content meta
			if ( $post->post_content !== '' ) {
				update_post_meta( $portfolio_id, '_nectar_portfolio_extra_content', $post->post_content );
			}

			// Copy portfolio categories/tags when present
			$cats = wp_get_object_terms( $post_id, 'portfolio_category', array( 'fields' => 'ids' ) );
			if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) {
				wp_set_object_terms( $portfolio_id, $cats, 'project-category', false );
			}
			$map   = get_option(
				'ap_portfolio_category_map',
				array(
					'artpulse_event'  => 'Event',
					'artpulse_artist' => 'Artist',
					'artpulse_org'    => 'Organization',
				)
			);
			$label = $map[ $post->post_type ] ?? '';
			if ( $label ) {
				$term = get_term_by( 'name', $label, 'project-category' );
				if ( ! $term ) {
					$term = wp_insert_term( $label, 'project-category' );
				}
				if ( ! is_wp_error( $term ) ) {
					$term_id = is_object( $term ) ? $term->term_id : ( $term['term_id'] ?? 0 );
					if ( $term_id ) {
						wp_set_object_terms( $portfolio_id, array( $term_id ), 'project-category', true );
					}
				}
			}
			$tags = wp_get_object_terms( $post_id, 'portfolio_tag', array( 'fields' => 'ids' ) );
			if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
				wp_set_object_terms( $portfolio_id, $tags, 'project-tag', false );
			}

			if ( $ids ) {
				set_post_thumbnail( $portfolio_id, $ids[0] );
			} else {
				$thumb = get_post_thumbnail_id( $post_id );
				if ( $thumb ) {
					set_post_thumbnail( $portfolio_id, $thumb );
				} else {
					delete_post_thumbnail( $portfolio_id );
					PortfolioSyncLogger::log( 'sync', 'Missing images', array( 'post' => $post_id ), get_current_user_id() );
				}
			}

			if ( $post->post_type === 'artpulse_event' ) {
				$meta_keys = array(
					'_ap_event_date',
					'_ap_event_venue',
					'_ap_event_start_time',
					'_ap_event_end_time',
				);
				foreach ( $meta_keys as $key ) {
					$val = get_post_meta( $post_id, $key, true );
					if ( $val !== '' ) {
						update_post_meta( $portfolio_id, $key, $val );
					} else {
						delete_post_meta( $portfolio_id, $key );
					}
				}

				// Link to artist/org portfolio posts when available
				$artist_ids        = (array) get_post_meta( $post_id, '_ap_event_artists', true );
				$org_id            = (int) get_post_meta( $post_id, '_ap_event_organization', true );
				$artist_portfolios = array();
				foreach ( $artist_ids as $aid ) {
					$pid = (int) get_post_meta( $aid, '_ap_portfolio_id', true );
					if ( $pid ) {
						$artist_portfolios[] = $pid;
					}
				}
				if ( $artist_portfolios ) {
					update_post_meta( $portfolio_id, '_ap_related_artists', $artist_portfolios );
				}
				if ( $org_id ) {
					$opid = (int) get_post_meta( $org_id, '_ap_portfolio_id', true );
					if ( $opid ) {
						update_post_meta( $portfolio_id, '_ap_related_org', $opid );
					}
				}
			}

			if ( $post->post_type === 'artpulse_org' ) {
				$org_keys = array(
					'ead_org_logo_id',
					'ead_org_banner_id',
					'ead_org_website_url',
					'ead_org_street_address',
					'ead_org_type',
				);
				foreach ( $org_keys as $key ) {
					$val = get_post_meta( $post_id, $key, true );
					if ( $val !== '' ) {
						update_post_meta( $portfolio_id, $key, $val );
					} else {
						delete_post_meta( $portfolio_id, $key );
					}
				}
			}
			PortfolioSyncLogger::log(
				'sync',
				'Synced portfolio',
				array(
					'post'      => $post_id,
					'portfolio' => $portfolio_id,
				),
				get_current_user_id()
			);
			self::$syncing = false;
		}
	}

	/**
	 * Remove the portfolio entry when the source post is deleted.
	 *
	 * @param int $post_id The source post ID being deleted.
	 */
	public static function delete_portfolio( $post_id ) {
		$type    = get_post_type( $post_id );
		$allowed = get_option(
			'ap_portfolio_sync_types',
			array(
				'artpulse_event',
				'artpulse_artist',
				'artpulse_org',
			)
		);
		$allowed = is_array( $allowed ) ? $allowed : array();
		if ( ! in_array( $type, $allowed, true ) ) {
			return;
		}

		$portfolio = get_posts(
			array(
				'post_type'   => 'portfolio',
				'meta_key'    => '_ap_source_post',
				'meta_value'  => $post_id,
				'post_status' => 'any',
				'numberposts' => 1,
				'fields'      => 'ids',
			)
		);
		if ( $portfolio ) {
			wp_delete_post( $portfolio[0], true );
		}
	}

	/**
	 * Sync Salient portfolio edits back to the source post.
	 */
	public static function sync_source( $post_id, $post ) {
		if ( self::$syncing || wp_is_post_revision( $post_id ) ) {
			return;
		}

		$source = (int) get_post_meta( $post_id, '_ap_source_post', true );
		if ( ! $source ) {
			return;
		}

		self::$syncing = true;

		$result = wp_update_post(
			array(
				'ID'           => $source,
				'post_title'   => $post->post_title,
				'post_content' => $post->post_content,
			),
			true
		);
		if ( is_wp_error( $result ) ) {
			PortfolioSyncLogger::log(
				'sync',
				'Source update failed',
				array(
					'portfolio' => $post_id,
					'error'     => $result->get_error_message(),
				),
				get_current_user_id()
			);
		}

		update_post_meta( $source, '_ap_portfolio_id', $post_id );

		$ids = get_post_meta( $post_id, '_ap_submission_images', true );
		if ( is_array( $ids ) ) {
			update_post_meta( $source, '_ap_submission_images', $ids );
		}

		PortfolioSyncLogger::log(
			'sync',
			'Synced source',
			array(
				'portfolio' => $post_id,
				'source'    => $source,
			),
			get_current_user_id()
		);
		self::$syncing = false;
	}

	/**
	 * Run a full synchronization of all source posts.
	 */
	public static function sync_all(): int {
		$types = get_option(
			'ap_portfolio_sync_types',
			array(
				'artpulse_event',
				'artpulse_artist',
				'artpulse_org',
			)
		);
		$types = is_array( $types ) ? $types : array();
		$count = 0;
		foreach ( $types as $type ) {
			$posts = get_posts(
				array(
					'post_type'      => $type,
					'posts_per_page' => -1,
					'post_status'    => 'any',
					'fields'         => 'ids',
				)
			);
			foreach ( $posts as $id ) {
				$post = get_post( $id );
				if ( $post ) {
					self::sync_portfolio( $id, $post );
					++$count;
				}
			}
		}
		PortfolioSyncLogger::log( 'sync', 'Bulk sync complete', array( 'count' => $count ), get_current_user_id() );
		return $count;
	}

	public static function cli_sync() {
		if ( ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			return;
		}
		if ( ! \wp_get_current_user()->has_cap( 'manage_options' ) ) {
			\WP_CLI::error( 'Insufficient permissions.' );
		}
		$count = self::sync_all();
		ap_clear_portfolio_cache();
		\WP_CLI::success( "Synced {$count} items." );
	}
}
