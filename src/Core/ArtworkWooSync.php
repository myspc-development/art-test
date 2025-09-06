<?php
namespace ArtPulse\Core;

use WC_Product_Simple;
use WP_Post;

class ArtworkWooSync {

	public static function register(): void {
		add_action( 'save_post_artpulse_artwork', array( self::class, 'sync_product' ), 20, 2 );
		add_action( 'woocommerce_order_status_completed', array( self::class, 'notify_purchase' ), 10, 1 );
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( self::class, 'tracking_fields' ) );
		add_action( 'woocommerce_process_shop_order_meta', array( self::class, 'save_tracking_fields' ) );
	}

	public static function sync_product( int $post_id, WP_Post $post ): void {
		if ( $post->post_type !== 'artpulse_artwork' || defined( 'DOING_AUTOSAVE' ) ) {
			return;
		}
		if ( ! class_exists( WC_Product_Simple::class ) ) {
			return;
		}

		$price = get_post_meta( $post_id, 'artwork_price', true );
		$stock = get_post_meta( $post_id, 'artwork_stock', true );

		$product_id = (int) get_post_meta( $post_id, '_woo_product_id', true );
		$product    = $product_id ? wc_get_product( $product_id ) : null;
		if ( ! $product ) {
			$product = new WC_Product_Simple();
			$product->set_name( $post->post_title );
			$product_id = $product->save();
			update_post_meta( $post_id, '_woo_product_id', $product_id );
			update_post_meta( $product_id, '_artwork_post_id', $post_id );
		}

		if ( $price !== '' ) {
			$product->set_regular_price( (float) $price );
		}
		if ( $stock !== '' ) {
			$product->set_manage_stock( true );
			$product->set_stock_quantity( (int) $stock );
		}
		$product->set_status( 'publish' );
		$product->save();
	}

	public static function tracking_fields( $order ): void {
		woocommerce_wp_text_input(
			array(
				'id'    => 'ap_tracking_provider',
				'label' => __( 'Tracking Provider', 'artpulse' ),
				'value' => $order->get_meta( 'ap_tracking_provider' ),
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'    => 'ap_tracking_number',
				'label' => __( 'Tracking Number', 'artpulse' ),
				'value' => $order->get_meta( 'ap_tracking_number' ),
			)
		);
	}

	public static function save_tracking_fields( int $order_id ): void {
		$order = wc_get_order( $order_id );
		$order->update_meta_data( 'ap_tracking_provider', sanitize_text_field( $_POST['ap_tracking_provider'] ?? '' ) );
		$order->update_meta_data( 'ap_tracking_number', sanitize_text_field( $_POST['ap_tracking_number'] ?? '' ) );
		$order->save();
	}

	public static function notify_purchase( $order_id ): void {
		if ( ! class_exists( 'WC_Order' ) ) {
			return;
		}
		$order = wc_get_order( $order_id );
		foreach ( $order->get_items() as $item ) {
			$product_id = $item->get_product_id();
			$artwork_id = (int) get_post_meta( $product_id, '_artwork_post_id', true );
			if ( ! $artwork_id ) {
				continue;
			}
			$artist_id = (int) get_post_field( 'post_author', $artwork_id );
			$artist    = get_userdata( $artist_id );
			if ( ! $artist ) {
				continue;
			}
			$buyer_email         = $order->get_billing_email();
			$tracking            = $order->get_meta( 'ap_tracking_number' );
			$provider            = $order->get_meta( 'ap_tracking_provider' );
			$tracking_info       = $tracking ? "\n" . sprintf( __( 'Tracking: %1$s %2$s', 'artpulse' ), $provider, $tracking ) : '';
			$title               = get_the_title( $artwork_id );
						$message = sprintf( esc_html__( 'Artwork %1$s purchased.', 'artpulse' ), esc_html( $title ) ) . $tracking_info;
						EmailService::send( $artist->user_email, __( 'Artwork Sold', 'artpulse' ), $message );
						EmailService::send( $buyer_email, __( 'Purchase Complete', 'artpulse' ), $message );
		}
	}
}
