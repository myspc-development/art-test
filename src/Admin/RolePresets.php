<?php
namespace ArtPulse\Admin;

use ArtPulse\Core\DashboardPresets;

/**
 * Expose widget preset slugs for roles with a JSON fallback.
 */
class RolePresets {
	/**
	 * Retrieve preset widget slugs for a role.
	 *
	 * @param string $role Role slug.
	 * @return array<int,string> List of canonical widget slugs.
	 */
	public static function get_preset_slugs( string $role ): array {
		return DashboardPresets::forRole( $role );
	}
}
