<?php
namespace ArtPulse\Core;

class PurchaseShortcode {
	public static function register() {
		ShortcodeRegistry::register( 'ap_membership_purchase', 'Membership Purchase', array( self::class, 'render' ) );
	}

	public static function render( $atts = array() ) {
		$atts = shortcode_atts(
			array(
				'level'       => 'Pro',
				'class'       => 'ap-purchase-link',
				'coupon'      => '',
				'show_coupon' => false,
			),
			$atts,
			'ap_membership_purchase'
		);

		$level       = sanitize_text_field( $atts['level'] );
		$show_coupon = filter_var( $atts['show_coupon'], FILTER_VALIDATE_BOOLEAN );
		$coupon      = sanitize_text_field( $atts['coupon'] );
		$url         = home_url( '/purchase-membership' );

		if ( function_exists( 'wc_get_checkout_url' ) ) {
			$url = add_query_arg( 'level', strtolower( $level ), wc_get_checkout_url() );
		} else {
			$url = add_query_arg( 'level', strtolower( $level ), $url );
		}

		if ( $coupon !== '' ) {
			$url = add_query_arg( 'coupon', rawurlencode( $coupon ), $url );
		}

                $label = sprintf( esc_html__( 'Purchase %1$s membership', 'artpulse' ), esc_html( $level ) );

		if ( $show_coupon ) {
			$placeholder = esc_attr__( 'Coupon code', 'artpulse' );
			$button      = esc_html( $label );
			$class       = esc_attr( $atts['class'] );
			$url         = esc_url( $url );
			return <<<HTML
<form class="ap-purchase-form" onsubmit="event.preventDefault();var c=this.querySelector('input[name=coupon]').value;window.location='{$url}&coupon='+encodeURIComponent(c);">
    <input type="text" name="coupon" placeholder="{$placeholder}">
    <button type="submit" class="{$class}">{$button}</button>
</form>
HTML;
		}

                return sprintf(
                        '<a href="%1$s" class="%2$s">%3$s</a>',
                        esc_url( $url ),
                        esc_attr( $atts['class'] ),
                        esc_html( $label )
                );
        }
}
