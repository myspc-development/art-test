<?php
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}
/**
 * Spotlight dashboard widget template.
 *
 * @package ArtPulse
 */

if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
		return;
}
if ( ! user_can( get_current_user_id(), 'read' ) ) {
		return;
}
use ArtPulse\Admin\SpotlightManager;

$category   = $args['category'] ?? null;
$widget_id  = $args['widget_id'] ?? 'role-spotlight';
$spotlights = array();
if ( ! defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
		$spotlights = SpotlightManager::get_dashboard_spotlights( $args['role'] ?? 'member', $category );
} else {
		echo '<p class="notice">' . esc_html__( 'Preview mode — dynamic content hidden', 'artpulse' ) . '</p>';
		return;
}

if ( empty( $spotlights ) ) {
	echo '<p class="ap-empty-state">' . esc_html__( 'No featured content available right now.', 'artpulse' ) . '</p>';
	return;
}
?>

<div id="<?php echo esc_attr( $widget_id ); ?>" class="ap-card" role="region" aria-labelledby="<?php echo esc_attr( $widget_id ); ?>-title" data-widget="<?php echo esc_attr( $widget_id ); ?>" data-widget-id="<?php echo esc_attr( $widget_id ); ?>">
		<h2 id="<?php echo esc_attr( $widget_id ); ?>-title" class="ap-card__title">🌟 <?php echo esc_html__( 'Featured for You', 'artpulse' ); ?></h2>
		<div>
		<?php foreach ( $spotlights as $spotlight ) : ?>
				<div class="ap-spotlight-card">
				<?php
				$terms = get_the_terms( $spotlight, 'spotlight_category' );
				if ( ! empty( $terms ) ) {
						echo '<span class="spotlight-tag">' . esc_html( $terms[0]->name ) . '</span>';
				}
				?>
				<strong><?php echo esc_html( $spotlight->post_title ); ?></strong><br>
				<p><?php echo esc_html( wp_trim_words( $spotlight->post_content, 20 ) ); ?></p>
				<?php
				$cta_text   = get_post_meta( $spotlight->ID, 'cta_text', true );
				$cta_url    = get_post_meta( $spotlight->ID, 'cta_url', true );
				$cta_target = get_post_meta( $spotlight->ID, 'cta_target', true );
				if ( $cta_text && $cta_url ) :
					?>
						<a href="<?php echo esc_url( $cta_url ); ?>" class="button small cta-button"
						<?php
						if ( '_blank' === $cta_target ) :
							?>
							target="_blank" rel="noopener noreferrer"<?php endif; ?>>
						<?php echo esc_html( $cta_text ); ?>
						</a>
				<?php endif; ?>
				</div>
		<?php endforeach; ?>
		</div>
</div>
