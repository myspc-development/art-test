<div id="ap-artist-comments" data-post-id="<?php the_ID(); ?>">
	<div class="ap-comments-list"></div>
	<?php if ( is_user_logged_in() ) : ?>
		<?php comment_form(); ?>
	<?php else : ?>
	<p><?php esc_html_e( 'Log in to comment', 'artpulse' ); ?></p>
	<?php endif; ?>
</div>
