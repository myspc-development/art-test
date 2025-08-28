<?php
namespace ArtPulse\Community;

class LeaderboardManager {

	public static function get_most_helpful( int $limit = 5 ): array {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_user_engagement_log';
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT user_id, COUNT(*) as cnt FROM $table WHERE type = %s GROUP BY user_id ORDER BY cnt DESC LIMIT %d",
				'favorite',
				$limit
			),
			ARRAY_A
		);

		$list = array();
		foreach ( $rows as $row ) {
			$user = get_user_by( 'id', $row['user_id'] );
			if ( $user ) {
				$list[] = array(
					'user_id'         => (int) $row['user_id'],
					'display_name'    => $user->display_name,
					'favorites_given' => (int) $row['cnt'],
				);
			}
		}
		return $list;
	}

	public static function get_most_upvoted( int $limit = 5 ): array {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_favorites';
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT object_id, object_type, COUNT(*) as cnt FROM $table GROUP BY object_id, object_type ORDER BY cnt DESC LIMIT %d",
				$limit
			),
			ARRAY_A
		);

		$list = array();
		foreach ( $rows as $row ) {
			$list[] = array(
				'object_id'   => (int) $row['object_id'],
				'object_type' => $row['object_type'],
				'title'       => FavoritesManager::get_object_title( $row['object_id'], $row['object_type'] ),
				'favorites'   => (int) $row['cnt'],
			);
		}
		return $list;
	}
}
