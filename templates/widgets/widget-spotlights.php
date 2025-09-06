<?php
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}
if ( ! user_can( get_current_user_id(), 'read' ) ) {
	return;
}
use ArtPulse\Admin\SpotlightManager;
use ArtPulse\Core\DashboardController;

$role       = DashboardController::get_role( get_current_user_id() );
$spotlights = SpotlightManager::get_dashboard_spotlights( $role );

if ( empty( $spotlights ) ) {
	echo '<p class="ap-empty-state">' . esc_html__( 'No featured content available right now.', 'artpulse' ) . '</p>';
	return;
}
?>
<div id="ap-spotlights" class="postbox" role="region" aria-labelledby="ap-spotlights-title">
	<h2 id="ap-spotlights-title" class="hndle"><span>🌟 <?php esc_html_e( 'Spotlights', 'artpulse' ); ?></span></h2>
	<div class="inside">
	<?php foreach ( $spotlights as $post ) : ?>
		<div class="ap-spotlight-card">
		<?php
		$terms = get_the_terms( $post, 'spotlight_category' );
		if ( ! empty( $terms ) ) {
			echo '<span class="spotlight-tag">' . esc_html( $terms[0]->name ) . '</span>';
		}
		?>
		<strong><?php echo esc_html( $post->post_title ); ?></strong><br>
		<p><?php echo wp_trim_words( $post->post_content, 20 ); ?></p>
		<?php
		$cta_text   = get_post_meta( $post->ID, 'cta_text', true );
		$cta_url    = get_post_meta( $post->ID, 'cta_url', true );
		$cta_target = get_post_meta( $post->ID, 'cta_target', true );
		if ( $cta_text && $cta_url ) :
			?>
			<a href="<?php echo esc_url( $cta_url ); ?>" class="button small cta-button" <?php echo $cta_target === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
			<?php echo esc_html( $cta_text ); ?>
			</a>
		<?php endif; ?>
		</div>
	<?php endforeach; ?>
	</div>
</div>
