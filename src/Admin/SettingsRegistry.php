<?php
namespace ArtPulse\Admin;

/**
 * Simple registry to collect settings tabs and fields from various managers.
 */
class SettingsRegistry {

	private static array $tabs   = array();
	private static array $fields = array();

	/**
	 * Register a settings tab.
	 */
	public static function register_tab( string $slug, string $label ): void {
		self::$tabs[ $slug ] = $label;
	}

	/**
	 * Register a field under a tab.
	 */
	public static function register_field( string $tab, string $key, array $config ): void {
		if ( ! isset( self::$fields[ $tab ] ) ) {
			self::$fields[ $tab ] = array();
		}
		self::$fields[ $tab ][ $key ] = $config;
	}

	/**
	 * Get all registered tabs.
	 */
	public static function get_tabs(): array {
		return self::$tabs;
	}

	/**
	 * Get fields registered for a tab.
	 */
	public static function get_fields( string $tab ): array {
		return self::$fields[ $tab ] ?? array();
	}
}
