<?php

namespace ArtPulse\Frontend;

class PortfolioBuilder {

	public static function register() {
		\ArtPulse\Core\ShortcodeRegistry::register( 'ap_portfolio_builder', 'Portfolio Builder', array( self::class, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_ap_save_portfolio', array( self::class, 'handle_form' ) );
		add_action( 'wp_ajax_ap_get_portfolio_item', array( self::class, 'get_item' ) );
		add_action( 'wp_ajax_ap_toggle_visibility', array( self::class, 'toggle_visibility' ) );
		add_action( 'wp_ajax_ap_delete_portfolio_item', array( self::class, 'delete_item' ) );
		add_action( 'wp_ajax_ap_save_portfolio_order', array( self::class, 'save_order' ) );
	}

	public static function enqueue_scripts() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		if ( function_exists( 'ap_enqueue_global_styles' ) ) {
			add_filter( 'ap_bypass_shortcode_detection', '__return_true' );
			ap_enqueue_global_styles();
		}

		wp_enqueue_media();

                // Load the SortableJS library locally for drag and drop ordering.
                $sortable_rel  = 'assets/libs/sortablejs/Sortable.min.js';
                $sortable_path = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . $sortable_rel;
                wp_enqueue_script(
                        'sortablejs',
                        plugins_url( $sortable_rel, ARTPULSE_PLUGIN_FILE ),
                        array(),
                        file_exists( $sortable_path ) ? (string) filemtime( $sortable_path ) : null,
                        true
                );

		wp_enqueue_script(
			'ap-portfolio-builder',
			plugins_url( '/assets/js/ap-portfolio-builder.js', ARTPULSE_PLUGIN_FILE ),
			array( 'jquery', 'sortablejs' ),
			'1.0',
			true
		);
		if ( function_exists( 'wp_script_add_data' ) ) {
			wp_script_add_data( 'ap-portfolio-builder', 'type', 'module' );
		}

		wp_localize_script(
			'ap-portfolio-builder',
			'APPortfolio',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'ap_portfolio_nonce' ),
			)
		);
	}

	public static function render() {
		if ( ! is_user_logged_in() ) {
			return '<p>You must be logged in to manage your portfolio.</p>';
		}

		ob_start();
		?>
		<div class="ap-form-messages" role="status" aria-live="polite"></div>
		<form id="ap-portfolio-form" class="ap-form-container">
			<h3>Create or Edit Portfolio Item</h3>
			<input type="hidden" name="post_id" value="" />
			<p><label class="ap-form-label" for="ap_portfolio_title">Title</label><br><input class="ap-input" id="ap_portfolio_title" type="text" name="title" required /></p>
			<p><label class="ap-form-label" for="ap_portfolio_description">Description</label><br><textarea class="ap-input" id="ap_portfolio_description" name="description" rows="3"></textarea></p>
			<p><label class="ap-form-label" for="ap_portfolio_category">Category</label><br>
				<select class="ap-input" id="ap_portfolio_category" name="category">
					<option value="painting">Painting</option>
					<option value="exhibition">Exhibition</option>
					<option value="award">Award</option>
				</select>
			</p>
			<p><label class="ap-form-label" for="ap_portfolio_link">Link (optional)</label><br><input class="ap-input" id="ap_portfolio_link" type="url" name="link" /></p>
			<p><label class="ap-form-label" for="ap_portfolio_visibility">Visibility</label><br>
				<select class="ap-input" id="ap_portfolio_visibility" name="visibility">
					<option value="public">Public</option>
					<option value="private">Private</option>
				</select>
			</p>
			<p>
				<button class="ap-form-button nectar-button" type="button" id="ap-upload-image">Upload Image</button><br>
				<img id="ap-preview" width="200" hidden />
				<input type="hidden" name="image_id" />
			</p>
			<p><label class="ap-form-label" for="ap_image_alt">Image ALT Text</label><br><input class="ap-input" id="ap_image_alt" type="text" name="image_alt" required /></p>
			<p><label><input type="checkbox" name="featured" value="1" /> Featured Item</label></p>
			<p><button class="ap-form-button nectar-button" type="submit">Save Portfolio Item</button></p>
			<p id="ap-portfolio-message" class="ap-form-messages" role="status" aria-live="polite"></p>
		</form>
		<hr>

		<h3>Your Saved Portfolio Items</h3>
		<div id="ap-saved-items" class="ap-widget">
			<?php
			$items = get_posts(
				array(
					'post_type'   => 'artpulse_portfolio',
					'author'      => get_current_user_id(),
					'post_status' => 'publish',
					'numberposts' => -1,
				)
			);

			foreach ( $items as $item ) :
				$visibility = get_post_meta( $item->ID, 'portfolio_visibility', true ) ?: 'public';
				$desc       = get_post_meta( $item->ID, 'portfolio_description', true );
				?>
				<div class="ap-saved-item" data-id="<?php echo esc_attr( $item->ID ); ?>">
					<strong><?php echo esc_html( $item->post_title ); ?></strong>
					<p><?php echo esc_html( $desc ); ?></p>
					<p>
						<button class="ap-form-button nectar-button edit-item">Edit</button>
						<button class="ap-form-button nectar-button toggle-visibility" data-new="<?php echo $visibility === 'private' ? 'public' : 'private'; ?>">
							<?php echo ucfirst( $visibility ); ?>
						</button>
						<button class="ap-form-button nectar-button delete-item">Delete</button>
					</p>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function handle_form() {
		check_ajax_referer( 'ap_portfolio_nonce', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array( 'message' => 'Permission denied.' ) );
		}

		$post_id    = intval( $_POST['post_id'] ?? 0 );
		$user_id    = get_current_user_id();
		$title      = sanitize_text_field( $_POST['title'] );
		$desc       = sanitize_text_field( $_POST['description'] );
		$cat        = sanitize_text_field( $_POST['category'] );
		$link       = esc_url_raw( $_POST['link'] );
		$visibility = sanitize_text_field( $_POST['visibility'] );
		$image_id   = intval( $_POST['image_id'] ?? 0 );
		$alt        = sanitize_text_field( $_POST['image_alt'] ?? '' );
		$featured   = ! empty( $_POST['featured'] ) ? 1 : 0;

		if ( $post_id && ( get_post_type( $post_id ) !== 'artpulse_portfolio' || get_post_field( 'post_author', $post_id ) != $user_id ) ) {
			wp_send_json_error( array( 'message' => 'Invalid portfolio item.' ) );
		}

		if ( $post_id ) {
			wp_update_post(
				array(
					'ID'         => $post_id,
					'post_title' => $title,
				)
			);
		} else {
			$post_id = wp_insert_post(
				array(
					'post_type'   => 'artpulse_portfolio',
					'post_title'  => $title,
					'post_status' => 'publish',
					'post_author' => $user_id,
				)
			);
		}

		if ( ! $post_id || is_wp_error( $post_id ) ) {
			wp_send_json_error( array( 'message' => 'Failed to save portfolio item.' ) );
		}

		if ( $image_id ) {
			$attachment = get_post( $image_id );
			if ( ! $attachment || $attachment->post_type !== 'attachment' || intval( $attachment->post_author ) !== $user_id ) {
				wp_send_json_error( array( 'message' => 'Invalid image.' ) );
			}
			if ( $alt === '' ) {
				wp_send_json_error( array( 'message' => 'ALT text required.' ) );
			}
			update_post_meta( $image_id, '_wp_attachment_image_alt', $alt );
		}

		wp_set_post_terms( $post_id, array( $cat ), 'portfolio_category' );
		update_post_meta( $post_id, 'portfolio_description', $desc );
		update_post_meta( $post_id, 'portfolio_link', $link );
		update_post_meta( $post_id, 'portfolio_visibility', $visibility );
		update_post_meta( $post_id, 'portfolio_image', $image_id );
		update_post_meta( $post_id, 'portfolio_featured', $featured );

		$profile_id = (int) get_user_meta( $user_id, 'ap_artist_profile_id', true );
		if ( $profile_id ) {
			update_post_meta( $post_id, '_ap_artist_profile', $profile_id );
			if ( $featured ) {
				update_post_meta( $profile_id, '_ap_portfolio_featured', $post_id );
			}
		}

		wp_send_json_success(
			array(
				'message' => 'Saved successfully.',
				'id'      => $post_id,
				'title'   => $title,
			)
		);
	}

	public static function get_item() {
		check_ajax_referer( 'ap_portfolio_nonce', 'nonce' );

		$id   = intval( $_GET['post_id'] );
		$post = get_post( $id );

		if ( ! $post || $post->post_author != get_current_user_id() || get_post_type( $post ) !== 'artpulse_portfolio' ) {
			wp_send_json_error( 'Not found or unauthorized' );
		}

		$image_id = (int) get_post_meta( $post->ID, 'portfolio_image', true );

		wp_send_json_success(
			array(
				'id'          => $post->ID,
				'title'       => $post->post_title,
				'description' => get_post_meta( $post->ID, 'portfolio_description', true ),
				'link'        => get_post_meta( $post->ID, 'portfolio_link', true ),
				'visibility'  => get_post_meta( $post->ID, 'portfolio_visibility', true ),
				'image_id'    => $image_id,
				'image_url'   => $image_id ? wp_get_attachment_url( $image_id ) : '',
				'category'    => wp_get_post_terms( $post->ID, 'portfolio_category', array( 'fields' => 'slugs' ) )[0] ?? '',
				'image_alt'   => $image_id ? get_post_meta( $image_id, '_wp_attachment_image_alt', true ) : '',
				'featured'    => (int) get_post_meta( $post->ID, 'portfolio_featured', true ),
			)
		);
	}

	public static function toggle_visibility() {
		check_ajax_referer( 'ap_portfolio_nonce', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( 'Permission denied' );
		}

		$id  = intval( $_POST['post_id'] );
		$new = sanitize_text_field( $_POST['visibility'] );

		if ( get_post_type( $id ) !== 'artpulse_portfolio' || get_post_field( 'post_author', $id ) != get_current_user_id() ) {
			wp_send_json_error( 'Not allowed' );
		}

		update_post_meta( $id, 'portfolio_visibility', $new );
		wp_send_json_success();
	}

	public static function delete_item() {
		check_ajax_referer( 'ap_portfolio_nonce', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( 'Permission denied' );
		}

		$id = intval( $_POST['post_id'] );

		if ( get_post_type( $id ) !== 'artpulse_portfolio' || get_post_field( 'post_author', $id ) != get_current_user_id() ) {
			wp_send_json_error( 'Not allowed' );
		}

		wp_delete_post( $id, true );
		wp_send_json_success();
	}

	public static function save_order() {
		check_ajax_referer( 'ap_portfolio_nonce', 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( 'Permission denied' );
		}

		$order = array_map( 'intval', $_POST['order'] ?? array() );

		foreach ( $order as $index => $post_id ) {
			if ( get_post_type( $post_id ) !== 'artpulse_portfolio' || get_post_field( 'post_author', $post_id ) != get_current_user_id() ) {
				continue;
			}
			wp_update_post(
				array(
					'ID'         => $post_id,
					'menu_order' => $index,
				)
			);
		}

		wp_send_json_success( array( 'message' => 'Order updated.' ) );
	}
}
