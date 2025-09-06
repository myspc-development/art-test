<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
namespace ArtPulse\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}

/**
 * Display sponsor information on event or post pages.
 */
class SponsorDisplayWidget {

	public static function register(): void {
		add_action( 'the_content', array( self::class, 'append_disclosure' ) );
	}

	public static function append_disclosure( string $content ): string {
		if ( ! is_singular() ) {
			return $content;
		}
		$id   = get_the_ID();
		$name = get_post_meta( $id, 'sponsor_name', true );
		$link = get_post_meta( $id, 'sponsor_link', true );
		$logo = get_post_meta( $id, 'sponsor_logo', true );
		if ( ! $name && ! $logo ) {
			return $content;
		}
		$out = '<div class="ap-sponsor-disclosure" data-widget-id="sponsor_display">';
		if ( $logo ) {
			$out .= '<img src="' . esc_url( $logo ) . '" alt="' . esc_attr( $name ) . '" />';
		}
		if ( $name ) {
			$label = $link ? '<a href="' . esc_url( $link ) . '" target="_blank" rel="sponsored">' . esc_html( $name ) . '</a>' : esc_html( $name );
			$out  .= '<p class="ap-sponsor-name">Sponsored by ' . $label . '</p>';
		}
		$out .= '</div>';
		return $content . $out;
	}
}

SponsorDisplayWidget::register();
