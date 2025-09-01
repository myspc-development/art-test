<?php
namespace ArtPulse\Integration;

use ArtPulse\Core\PortfolioSyncLogger;
use ArtPulse\Integration\PortfolioSync;

use WP_CLI;

/**
 * Simple WP-CLI command to migrate existing plugin portfolio items
 * to Salient portfolio posts.
 */
class PortfolioMigration {

	public static function register() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'ap migrate-portfolio', array( self::class, 'migrate' ) );
		}
	}

	/**
	 * Copy artpulse_portfolio posts into Salient portfolio CPT.
	 *
	 * @wp-cli-command migrate-portfolio
	 */
	public static function migrate( $interactive = false ) {
		$posts = get_posts(
			array(
				'post_type'      => 'artpulse_portfolio',
				'posts_per_page' => -1,
				'post_status'    => 'any',
			)
		);

		$count = 0;

		foreach ( $posts as $post ) {
			$exists = get_posts(
				array(
					'post_type'   => 'portfolio',
					'meta_key'    => '_ap_source_post',
					'meta_value'  => $post->ID,
					'post_status' => 'any',
					'numberposts' => 1,
					'fields'      => 'ids',
				)
			);

			if ( $exists ) {
				continue;
			}

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
			if ( is_wp_error( $portfolio_id ) ) {
				PortfolioSyncLogger::log(
					'migration',
					'Insert failed',
					array(
						'post'  => $post->ID,
						'error' => $portfolio_id->get_error_message(),
					)
				);
				if ( $interactive && defined( 'WP_CLI' ) && WP_CLI ) {
					\WP_CLI::warning( "Failed to insert portfolio for {$post->ID}" );
				}
				continue;
			}

			update_post_meta( $portfolio_id, '_ap_source_post', $post->ID );
			update_post_meta( $post->ID, '_ap_portfolio_id', $portfolio_id );
			++$count;
			PortfolioSyncLogger::log(
				'migration',
				'Migrated post',
				array(
					'post'      => $post->ID,
					'portfolio' => $portfolio_id,
				)
			);
		}
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
			$posts = get_posts(
				array(
					'post_type'      => $type,
					'posts_per_page' => -1,
					'post_status'    => 'any',
					'fields'         => 'ids',
				)
			);
			foreach ( $posts as $pid ) {
				$post = get_post( $pid );
				if ( $post ) {
					PortfolioSync::sync_portfolio( $pid, $post );
					++$count;
				}
			}
		}

		PortfolioSyncLogger::log( 'migration', 'Migration complete', array( 'count' => $count ) );
		if ( $interactive && defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::success( "Portfolio migration complete ({$count})" );
		}
		return $count;
	}
}
