<?php
namespace ArtPulse\Core;

use ArtPulse\Core\DashboardWidgetRegistry;

if ( ! defined( 'ABSPATH' ) ) {
		exit;
}

/**
 * Helper utilities for dashboard layout normalization and style merging.
 */
class LayoutUtils {

	/**
	 * Normalize a raw layout array to the schema
	 * [ ['id' => 'widget', 'visible' => bool ] ].
	 *
	 * Invalid widget IDs are preserved but noted in the $logs array. Duplicate
	 * entries are removed while keeping the first occurrence.
	 *
	 * @param array $layout    Raw layout array.
	 * @param array $valid_ids List of valid widget IDs.
	 * @param array $logs      Reference array capturing invalid IDs.
	 *
	 * @return array<int,array{id:string,visible:bool}>
	 */
	public static function normalize_layout( array $layout, array $valid_ids, array &$logs = array() ): array {
		$normalized = array();
		$seen       = array();

		foreach ( $layout as $item ) {
			if ( is_array( $item ) && isset( $item['id'] ) ) {
						$raw = (string) $item['id'];
						$vis = isset( $item['visible'] ) ? (bool) $item['visible'] : true;
			} else {
							$raw = (string) $item;
							$vis = true;
			}

							$canon = DashboardWidgetRegistry::canon_slug( $raw );

			if ( in_array( $canon, $valid_ids, true ) ) {
				$id = $canon;
			} else {
				$id = strtolower( $raw );
				$id = str_replace( '-', '_', $id );
				$id = preg_replace( '/[^a-z0-9_]/', '', $id );
				$id = trim( $id, '_' );
			}

			if ( $id === '' || isset( $seen[ $id ] ) ) {
				continue; // drop duplicates or empties
			}
							$seen[ $id ] = true;

			if ( ! in_array( $id, $valid_ids, true ) ) {
				$logs[] = $id;
			}

							$normalized[] = array(
								'id'      => $id,
								'visible' => $vis,
							);
		}

		return $normalized;
	}

	/**
	 * Merge two style arrays, overwriting keys from the base with $updates.
	 */
	public static function merge_styles( array $base, array $updates ): array {
		$clean = array();
		foreach ( array_merge( $base, $updates ) as $k => $v ) {
			$key           = sanitize_key( $k );
			$val           = is_string( $v ) ? sanitize_text_field( $v ) : $v;
			$clean[ $key ] = $val;
		}
		return $clean;
	}
}
