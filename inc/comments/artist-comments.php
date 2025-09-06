<?php
if ( post_password_required() ) {
	return;
}
?>
<div id="comments" class="artist-comments">
	<?php if ( have_comments() ) : ?>
		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
				)
			);
			?>
		</ol>
	<?php endif; ?>

	<?php if ( is_user_logged_in() ) : ?>
		<?php comment_form(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Log in to comment', 'artpulse' ); ?></p>
	<?php endif; ?>
</div>
