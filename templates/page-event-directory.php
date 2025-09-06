<?php
/**
 * Template Name: Event Directory
 */
get_header();

$type     = sanitize_text_field( $_GET['type'] ?? '' );
$location = sanitize_text_field( $_GET['location'] ?? '' );
$date     = sanitize_text_field( $_GET['date'] ?? '' );

$args = array(
	'post_type'      => 'artpulse_event',
	'posts_per_page' => -1,
	'meta_query'     => array(),
	'tax_query'      => array(),
	'orderby'        => 'meta_value',
	'meta_key'       => 'event_start_date',
	'order'          => 'ASC',
);

if ( $type ) {
	$args['tax_query'][] = array(
		'taxonomy' => 'event_type',
		'field'    => 'slug',
		'terms'    => $type,
	);
}
if ( $location ) {
	$args['meta_query'][] = array(
		'key'     => 'event_city',
		'value'   => $location,
		'compare' => 'LIKE',
	);
}
if ( $date ) {
	$args['meta_query'][] = array(
		'key'     => 'event_start_date',
		'value'   => $date,
		'compare' => '>=',
		'type'    => 'DATE',
	);
}

$query = new WP_Query( $args );
?>
<div class="ap-directory-filters">
	<form method="get">
	<label><?php _e( 'Type', 'artpulse' ); ?> <input type="text" name="type" value="<?php echo esc_attr( $type ); ?>"></label>
	<label><?php _e( 'Date', 'artpulse' ); ?> <input type="date" name="date" value="<?php echo esc_attr( $date ); ?>"></label>
	<label><?php _e( 'Location', 'artpulse' ); ?> <input type="text" name="location" value="<?php echo esc_attr( $location ); ?>"></label>
	<button type="submit"><?php _e( 'Filter', 'artpulse' ); ?></button>
	</form>
</div>
<div class="ap-event-directory">
<?php if ( $query->have_posts() ) : ?>
	<?php
	while ( $query->have_posts() ) :
		$query->the_post();
		?>
		<?php $event_id = get_the_ID(); ?>
	<div class="ap-event-card-wrap">
		<?php
		$event_id = get_the_ID();
		ap_safe_include( 'templates/event-card.php', plugin_dir_path( __FILE__ ) . 'event-card.php' );
		?>
	</div>
	<?php endwhile; ?>
	<?php wp_reset_postdata(); ?>
<?php else : ?>
	<p><?php _e( 'No events found.', 'artpulse' ); ?></p>
<?php endif; ?>
</div>
<?php get_footer(); ?>
