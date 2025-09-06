<?php
/**
 * Stats overview section.
 *
 * Expected variable: $stats (array of objects with ->name).
 */
?>
<div class="ap-card" role="region" aria-labelledby="ap-dashboard-stats-title" id="ap-dashboard-stats">
	<h2 id="ap-dashboard-stats-title" class="ap-card__title"><?php esc_html_e( 'Stats', 'artpulse' ); ?></h2>
<?php if ( ! empty( $stats ) ) : ?>
	<ul class="ap-dashboard-stats-list">
	<?php foreach ( $stats as $item ) : ?>
	<li><?php echo esc_html( $item->name ); ?></li>
	<?php endforeach; ?>
	</ul>
<?php else : ?>
	<?php include __DIR__ . '/dashboard-empty-state.php'; ?>
<?php endif; ?>
</div>
