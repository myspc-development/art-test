<?php
namespace ArtPulse\Frontend;

/**
 * Render a donation button if the user has a donation URL.
 */
function ap_render_donate_button( int $user_id, string $text = '' ): string {
	$url = get_user_meta( $user_id, 'donation_url', true );
	if ( ! $url ) {
		return '';
	}
	if ( ! $text ) {
		$text = __( 'Support this Artist', 'artpulse' );
	}
	$html = sprintf(
		'<a class="ap-donate-btn nectar-button" href="%s" target="_blank" rel="noopener noreferrer">&#10084; %s</a>',
		esc_url( $url ),
		esc_html( $text )
	);
	return $html;
}
