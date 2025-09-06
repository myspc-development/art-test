<?php
/**
 * Shortcode to render artist comments container and form.
 */
function ap_artist_comments_shortcode(): string {
	$post_id = get_the_ID();
	if ( ! $post_id ) {
		return '';
	}
	ob_start();
	?>
	<div id="ap-artist-comments" data-post-id="<?php echo esc_attr( $post_id ); ?>">
		<div class="ap-comments-list"></div>
		<?php if ( is_user_logged_in() ) : ?>
			<?php comment_form(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Log in to comment', 'artpulse' ); ?></p>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}
\ArtPulse\Core\ShortcodeRegistry::register( 'ap_artist_comments', 'Artist Comments', 'ap_artist_comments_shortcode' );
