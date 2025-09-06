<?php
/** Single template for ArtPulse Artist with comments and follow button */
get_header();
if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		echo '<div class="container single-artist-content">';
		if ( has_post_thumbnail() ) {
			echo '<div class="artist-featured-image">';
			the_post_thumbnail( 'large' );
			echo '</div>';
		}
		$followers = (int) get_user_meta( get_the_author_meta( 'ID' ), 'ap_follower_count', true );
		echo '<h1 class="entry-title artist-title">' . esc_html( get_the_title() ) . ' <span class="ap-followers-badge">' . esc_html( $followers ) . ' ' . esc_html__( 'followers', 'artpulse' ) . '</span></h1>';
		echo '<div class="entry-content">';
		the_content();
		echo '</div>';
		?>
	<form class="ap-newsletter-optin">
		<input type="email" placeholder="<?php esc_attr_e( 'Your email', 'artpulse' ); ?>" required>
		<button type="submit"><?php esc_html_e( 'Subscribe', 'artpulse' ); ?></button>
		<span class="ap-optin-message"></span>
	</form>
		<?php
		$donate = \ArtPulse\Frontend\ap_render_donate_button( get_the_author_meta( 'ID' ) );
		if ( $donate ) {
			echo wp_kses_post( $donate );
		}

		echo \ArtPulse\Frontend\ap_share_buttons( get_permalink(), get_the_title(), get_post_type(), get_the_ID() );
		?>
		<?php
		comments_template( '/inc/comments/artist-comments.php' );
		echo '</div>';
	endwhile;
endif;
get_footer();
