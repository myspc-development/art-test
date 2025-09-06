<?php
/**
 * Upcoming events section.
 *
 * Expected variable: $events (array of event objects with ->name).
 */
?>
<div class="ap-card" role="region" aria-labelledby="ap-events-title" id="ap-events">
	<h2 id="ap-events-title" class="ap-card__title"><?php esc_html_e( 'Upcoming Events', 'artpulse' ); ?></h2>
<?php if ( ! empty( $events ) ) : ?>
	<ul class="ap-events-list">
	<?php foreach ( $events as $event ) : ?>
	<li><?php echo esc_html( $event->name ); ?></li>
	<?php endforeach; ?>
	</ul>
<?php else : ?>
	<?php include __DIR__ . '/dashboard-empty-state.php'; ?>
<?php endif; ?>
</div>
