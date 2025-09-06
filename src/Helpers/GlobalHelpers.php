<?php
namespace ArtPulse\Helpers;

class GlobalHelpers {
	public static function pageHasArtpulseShortcode(): bool {
		if ( ! is_singular() ) {
			return false;
		}
		global $post;
		if ( ! $post || empty( $post->post_content ) ) {
			return false;
		}
		return strpos( $post->post_content, '[ap_' ) !== false;
	}

	public static function pageHasShortcode( string $tag ): bool {
		if ( ! is_singular() ) {
			return false;
		}
		global $post;
		if ( ! $post || empty( $post->post_content ) ) {
			return false;
		}
		return has_shortcode( $post->post_content, $tag );
	}

	public static function getAccentColor(): string {
		return get_theme_mod( 'accent_color', '#0073aa' );
	}

	public static function adjustColorBrightness( string $hex, float $percent ): string {
		$hex = ltrim( $hex, '#' );
		if ( strlen( $hex ) === 3 ) {
			$hex = str_repeat( substr( $hex, 0, 1 ), 2 ) .
					str_repeat( substr( $hex, 1, 1 ), 2 ) .
					str_repeat( substr( $hex, 2, 1 ), 2 );
		}
		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );
		$r = max( 0, min( 255, (int) ( $r * ( 1 + $percent ) ) ) );
		$g = max( 0, min( 255, (int) ( $g * ( 1 + $percent ) ) ) );
		$b = max( 0, min( 255, (int) ( $b * ( 1 + $percent ) ) ) );
		return sprintf( '#%02x%02x%02x', $r, $g, $b );
	}

	public static function stylesDisabled(): bool {
		$settings = get_option( 'artpulse_settings', array() );
		return ! empty( $settings['disable_styles'] );
	}

	public static function wpAdminAccessEnabled(): bool {
		$settings = get_option( 'artpulse_settings', array() );
		return ! empty( $settings['enable_wp_admin_access'] );
	}

	public static function enqueueGlobalStyles(): void {
		if ( is_admin() ) {
			return;
		}
		$bypass = apply_filters( 'ap_bypass_shortcode_detection', false );
		if ( $bypass || self::pageHasArtpulseShortcode() ) {
			$accent = self::getAccentColor();
			$hover  = self::adjustColorBrightness( $accent, -0.1 );
			wp_add_inline_style(
				'ap-complete-dashboard-style',
				":root { --ap-primary: {$accent}; --ap-primary-hover: {$hover}; }"
			);
		}
	}
}
