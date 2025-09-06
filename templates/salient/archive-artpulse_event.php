<?php
/**
 * Archive template for ArtPulse Events.
 * Place in: /wp-content/themes/salient-child/archive-artpulse_event.php
 */

get_header(); ?>

<div class="container-wrap">
	<div class="container main-content">
	<div class="row">
		<div class="col span_12 section-title">
		<h1><?php post_type_archive_title(); ?></h1>
		</div>

		<?php if ( have_posts() ) : ?>
		<div class="event-archive-grid">

			<?php
			while ( have_posts() ) :
				the_post();
				?>
			<div class="event-card">
				<a href="<?php the_permalink(); ?>" class="event-card-link">

				<?php if ( has_post_thumbnail() ) : ?>
					<div class="event-card-thumbnail">
					<?php the_post_thumbnail( 'medium' ); ?>
					</div>
				<?php endif; ?>

				<div class="event-card-content">
					<h2 class="event-card-title"><?php the_title(); ?></h2>

					<?php
					$date  = get_post_meta( get_the_ID(), '_ap_event_date', true );
					$venue = get_post_meta( get_the_ID(), '_ap_event_venue', true );
					?>

					<?php if ( $date ) : ?>
					<p class="event-card-date"><strong><?php esc_html_e( 'Date:', 'artpulse' ); ?></strong> <?php echo esc_html( $date ); ?></p>
					<?php endif; ?>

					<?php if ( $venue ) : ?>
					<p class="event-card-venue"><strong><?php esc_html_e( 'Venue:', 'artpulse' ); ?></strong> <?php echo esc_html( $venue ); ?></p>
					<?php endif; ?>
				</div>
				</a>
			</div>
			<?php endwhile; ?>

		</div>

		<div class="pagination">
			<?php echo paginate_links(); ?>
		</div>

		<?php else : ?>
		<p><?php esc_html_e( 'No events found.', 'artpulse' ); ?></p>
		<?php endif; ?>
	</div>
	</div>
</div>

<?php get_footer(); ?>
