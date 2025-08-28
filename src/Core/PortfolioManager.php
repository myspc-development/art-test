<?php

namespace ArtPulse\Core;

class PortfolioManager {

	public static function register() {
		add_action( 'init', array( self::class, 'registerPortfolioPostType' ) );
		add_action( 'init', array( self::class, 'registerPortfolioTaxonomy' ) );
		add_action( 'add_meta_boxes', array( self::class, 'addPortfolioMetaBoxes' ) );
		add_action( 'save_post', array( self::class, 'savePortfolioMeta' ) );
		if ( is_admin() ) {
			add_action( 'admin_init', array( self::class, 'maybe_migrate_meta' ) );
		}
	}

	public static function registerPortfolioPostType() {
		register_post_type(
			'artpulse_portfolio',
			array(
				'labels'        => array(
					'name'          => __( 'Portfolios', 'artpulse' ),
					'singular_name' => __( 'Portfolio', 'artpulse' ),
					'add_new'       => __( 'Add New', 'artpulse' ),
					'add_new_item'  => __( 'Add New Portfolio', 'artpulse' ),
					'edit_item'     => __( 'Edit Portfolio', 'artpulse' ),
					'new_item'      => __( 'New Portfolio', 'artpulse' ),
					'view_item'     => __( 'View Portfolio', 'artpulse' ),
					'all_items'     => __( 'All Portfolios', 'artpulse' ),
				),
				'public'        => true,
				'has_archive'   => true,
				'menu_position' => 27,
				'menu_icon'     => 'dashicons-portfolio',
				'supports'      => array( 'title', 'editor', 'thumbnail', 'author' ),
				'rewrite'       => array( 'slug' => 'portfolios' ),
				'show_in_rest'  => true,
			)
		);
	}

	public static function registerPortfolioTaxonomy() {
		register_taxonomy(
			'portfolio_category',
			'artpulse_portfolio',
			array(
				'label'        => __( 'Portfolio Categories', 'artpulse' ),
				'public'       => true,
				'hierarchical' => true,
				'show_in_rest' => true,
				'rewrite'      => array( 'slug' => 'portfolio-category' ),
			)
		);
	}

	public static function addPortfolioMetaBoxes() {
		add_meta_box( 'ap_portfolio_link', __( 'External Link', 'artpulse' ), array( self::class, 'renderLinkMetaBox' ), 'artpulse_portfolio', 'normal', 'default' );
		add_meta_box( 'ap_portfolio_visibility', __( 'Visibility', 'artpulse' ), array( self::class, 'renderVisibilityMetaBox' ), 'artpulse_portfolio', 'side', 'default' );

		$path = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'includes/artist-meta-box.php';
		if ( file_exists( $path ) ) {
			require_once $path;
			if ( function_exists( 'ap_artist_portfolio_metaboxes_register' ) ) {
				ap_artist_portfolio_metaboxes_register();
				add_action( 'save_post_artpulse_portfolio', 'ap_save_artist_portfolio_meta', 10, 2 );
			}
		}
	}

	public static function renderLinkMetaBox( $post ) {
		$link = get_post_meta( $post->ID, '_ap_portfolio_link', true );
		wp_nonce_field( 'ap_portfolio_meta_nonce', 'ap_portfolio_meta_nonce_field' );
		echo '<input type="url" name="ap_portfolio_link" value="' . esc_attr( $link ) . '" class="widefat" placeholder="https://..." />';
	}

	public static function renderVisibilityMetaBox( $post ) {
		$visibility = get_post_meta( $post->ID, '_ap_visibility', true );
		?>
		<select name="ap_visibility" class="widefat">
			<option value="public" <?php selected( $visibility, 'public' ); ?>><?php esc_html_e( 'Public', 'artpulse' ); ?></option>
			<option value="private" <?php selected( $visibility, 'private' ); ?>><?php esc_html_e( 'Private (admin only)', 'artpulse' ); ?></option>
			<option value="members" <?php selected( $visibility, 'members' ); ?>><?php esc_html_e( 'Members Only', 'artpulse' ); ?></option>
		</select>
		<?php
	}

	public static function savePortfolioMeta( $post_id ) {
		if ( ! isset( $_POST['ap_portfolio_meta_nonce_field'] ) || ! wp_verify_nonce( $_POST['ap_portfolio_meta_nonce_field'], 'ap_portfolio_meta_nonce' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['ap_portfolio_link'] ) ) {
			update_post_meta( $post_id, '_ap_portfolio_link', esc_url_raw( $_POST['ap_portfolio_link'] ) );
		}

		if ( isset( $_POST['ap_visibility'] ) ) {
			update_post_meta( $post_id, '_ap_visibility', sanitize_text_field( $_POST['ap_visibility'] ) );
		}
	}

	public static function maybe_migrate_meta() {
		if ( get_option( 'ap_portfolio_meta_migrated' ) ) {
			return;
		}

		$posts = get_posts(
			array(
				'post_type'      => 'artpulse_portfolio',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'     => '_ap_portfolio_link',
						'compare' => 'EXISTS',
					),
					array(
						'key'     => '_ap_visibility',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		foreach ( $posts as $post_id ) {
			$link = get_post_meta( $post_id, '_ap_portfolio_link', true );
			if ( $link && ! get_post_meta( $post_id, 'portfolio_link', true ) ) {
				update_post_meta( $post_id, 'portfolio_link', $link );
			}

			$visibility = get_post_meta( $post_id, '_ap_visibility', true );
			if ( $visibility && ! get_post_meta( $post_id, 'portfolio_visibility', true ) ) {
				update_post_meta( $post_id, 'portfolio_visibility', $visibility );
			}

			delete_post_meta( $post_id, '_ap_portfolio_link' );
			delete_post_meta( $post_id, '_ap_visibility' );
		}

		update_option( 'ap_portfolio_meta_migrated', 1 );
	}
}
