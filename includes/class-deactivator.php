<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles plugin deactivation cleanup tasks.
 */
class ArtPulse_Deactivator {

	/**
	 * Run on plugin deactivation.
	 */
	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
