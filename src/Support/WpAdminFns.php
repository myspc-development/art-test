<?php
namespace ArtPulse\Support;

class WpAdminFns {
	/** Ensure admin template functions are available. */
	protected static function load_template_functions(): void {
		if ( ! function_exists( 'submit_button' ) ) {
			require_once ABSPATH . 'wp-admin/includes/template.php';
		}
	}

	/** Echoes a WP-style submit button on frontend or admin. */
	public static function submit_button( $text = null, $type = 'primary', $name = 'submit', $wrap = true, $other = null ): void {
		self::load_template_functions();
		\submit_button( $text, $type, $name, $wrap, $other );
	}

	/** Output hidden fields for a settings group. */
	public static function settings_fields( string $option_group ): void {
		self::load_template_functions();
		\settings_fields( $option_group );
	}

	/** Display settings sections for a settings page. */
	public static function do_settings_sections( string $page ): void {
		self::load_template_functions();
		\do_settings_sections( $page );
	}
}
