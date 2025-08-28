<?php
namespace ArtPulse\Services;

use ArtPulse\Admin\SpotlightManager;

class RecommendationService {

	public static function get_for_user( int $user_id ): array {
		$items = array();

		// Recent events near user
		$event_args = array(
			'post_type'      => 'artpulse_event',
			'posts_per_page' => 3,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);
		$events     = get_posts( $event_args );
		foreach ( $events as $e ) {
			$items[] = array(
				'type'  => 'event',
				'id'    => $e->ID,
				'title' => $e->post_title,
				'link'  => get_permalink( $e ),
			);
		}

		// Spotlights for user's role
		$role       = \ArtPulse\Core\DashboardController::get_role( $user_id );
		$spotlights = SpotlightManager::get_dashboard_spotlights( $role );
		foreach ( $spotlights as $p ) {
			$items[] = array(
				'type'  => 'spotlight',
				'id'    => $p->ID,
				'title' => $p->post_title,
				'link'  => get_permalink( $p ),
			);
		}

		// Followed artists
		$followed = get_user_meta( $user_id, 'followed_artists', true );
		$followed = array_filter( array_map( 'intval', (array) $followed ) );
		if ( $followed ) {
			$artists = get_users( array( 'include' => $followed ) );
			foreach ( $artists as $a ) {
				$items[] = array(
					'type'  => 'artist',
					'id'    => $a->ID,
					'title' => $a->display_name,
					'link'  => get_author_posts_url( $a->ID ),
				);
			}
		}

		return $items;
	}
}
