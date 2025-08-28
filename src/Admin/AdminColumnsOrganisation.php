<?php
namespace ArtPulse\Admin;

class AdminColumnsOrganisation {

	public static function register() {
		add_filter( 'manage_artpulse_org_posts_columns', array( __CLASS__, 'add_columns' ) );
		add_action( 'manage_artpulse_org_posts_custom_column', array( __CLASS__, 'render_columns' ), 10, 2 );
		add_filter( 'manage_edit-artpulse_org_sortable_columns', array( __CLASS__, 'make_sortable' ) );
	}

	public static function add_columns( array $columns ): array {
		$new = array();
		foreach ( $columns as $key => $label ) {
			if ( 'cb' === $key ) {
				$new['cb']                  = $label;
				$new['logo']                = __( 'Logo', 'artpulse' );
				$new['ead_org_name']        = __( 'Name', 'artpulse' );
				$new['ead_org_description'] = __( 'Description', 'artpulse' );
				$new['ead_org_type']        = __( 'Type', 'artpulse' );
				$new['ead_org_website_url'] = __( 'Website', 'artpulse' );
				$new['ead_org_banner_url']  = __( 'Banner', 'artpulse' );
				$new['ead_org_geo_lat']     = __( 'Latitude', 'artpulse' );
				$new['ead_org_geo_lng']     = __( 'Longitude', 'artpulse' );
			}
			$new[ $key ] = $label;
		}
		return $new;
	}

	public static function render_columns( string $column, int $post_id ) {
		switch ( $column ) {
			case 'logo':
				$url = get_post_meta( $post_id, 'ead_org_logo_url', true );
				if ( $url ) {
					printf(
						'<a href="%1$s" target="_blank"><img src="%1$s" /></a>',
						esc_url( $url )
					);
				} else {
					echo '&mdash;';
				}
				break;

			case 'ead_org_name':
				$name = get_post_meta( $post_id, 'ead_org_name', true );
				echo esc_html( $name ?: get_the_title( $post_id ) );
				break;

			case 'ead_org_description':
				$desc = get_post_meta( $post_id, 'ead_org_description', true );
				echo esc_html( $desc ?: '—' );
				break;

			case 'ead_org_type':
				$type = get_post_meta( $post_id, 'ead_org_type', true );
				echo esc_html( $type ?: '—' );
				break;

			case 'ead_org_website_url':
				$url = get_post_meta( $post_id, 'ead_org_website_url', true );
				if ( $url ) {
					printf( '<a href="%1$s" target="_blank">%2$s</a>', esc_url( $url ), esc_html( $url ) );
				} else {
					echo '&mdash;';
				}
				break;

			case 'ead_org_banner_url':
				$url = get_post_meta( $post_id, 'ead_org_banner_url', true );
				if ( $url ) {
					printf(
						'<a href="%1$s" target="_blank"><img src="%1$s" /></a>',
						esc_url( $url )
					);
				} else {
					echo '&mdash;';
				}
				break;

			case 'ead_org_geo_lat':
				$lat = get_post_meta( $post_id, 'ead_org_geo_lat', true );
				echo esc_html( $lat ?: '—' );
				break;

			case 'ead_org_geo_lng':
				$lng = get_post_meta( $post_id, 'ead_org_geo_lng', true );
				echo esc_html( $lng ?: '—' );
				break;
		}
	}

	public static function make_sortable( array $columns ): array {
		$columns['ead_org_name'] = 'ead_org_name';
		$columns['ead_org_type'] = 'ead_org_type';
		return $columns;
	}
}

AdminColumnsOrganisation::register();
