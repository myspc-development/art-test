<?php
namespace ArtPulse\Frontend;

use ArtPulse\Support\OptionUtils;

class WidgetEmbedShortcode {
	public static function register() {
		if ( ! shortcode_exists( 'ap_widget' ) ) {
			\ArtPulse\Core\ShortcodeRegistry::register( 'ap_widget', 'Dashboard Widget', array( __CLASS__, 'render' ) );
		}
	}

	public static function render( $atts = array() ) {
		$atts = shortcode_atts(
			array(
				'id' => '',
			),
			$atts
		);

		$widget_id = sanitize_text_field( $atts['id'] );
		if ( ! $widget_id ) {
			return '';
		}

		$user = wp_get_current_user();
		$role = $user->roles[0] ?? 'guest';

		// Load widget config
		$registry = OptionUtils::get_array_option( 'artpulse_widget_roles' );
		$conf     = $registry[ $widget_id ] ?? array();
		if ( is_array( $conf ) && array_keys( $conf ) !== range( 0, count( $conf ) - 1 ) ) {
			$allowed_roles = (array) ( $conf['roles'] ?? array() );
		} else {
			$allowed_roles = (array) $conf;
		}

		if ( ! in_array( $role, $allowed_roles, true ) ) {
			return '';
		}

		$template_path = plugin_dir_path( __FILE__ ) . "../../templates/widgets/{$widget_id}.php";
		if ( ! file_exists( $template_path ) ) {
			return '';
		}

		ob_start();
		include $template_path;
		return ob_get_clean();
	}
}
