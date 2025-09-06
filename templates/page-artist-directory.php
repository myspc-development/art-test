<?php
/**
 * Template Name: Artist Directory
 */
get_header();

$tag      = sanitize_text_field( $_GET['tag'] ?? '' );
$location = sanitize_text_field( $_GET['location'] ?? '' );

$args = array(
	'post_type'      => 'artpulse_artist',
	'posts_per_page' => -1,
	'orderby'        => 'title',
	'order'          => 'ASC',
);

$tax_query  = array();
$meta_query = array();

if ( $tag ) {
	$tax_query[] = array(
		'taxonomy' => 'artist_specialty',
		'field'    => 'slug',
		'terms'    => $tag,
	);
}
if ( $location ) {
	$meta_query[] = array(
		'key'     => 'ap_country',
		'value'   => $location,
		'compare' => 'LIKE',
	);
}
if ( $tax_query ) {
	$args['tax_query'] = $tax_query; }
if ( $meta_query ) {
	$args['meta_query'] = $meta_query; }

$query = new WP_Query( $args );
?>
<div class="ap-directory-filters">
	<form method="get">
	<label><?php _e( 'Tag', 'artpulse' ); ?> <input type="text" name="tag" value="<?php echo esc_attr( $tag ); ?>"></label>
	<label><?php _e( 'Location', 'artpulse' ); ?> <input type="text" name="location" value="<?php echo esc_attr( $location ); ?>"></label>
	<button type="submit"><?php _e( 'Filter', 'artpulse' ); ?></button>
	</form>
</div>
<div class="ap-artist-directory">
<?php if ( $query->have_posts() ) : ?>
	<?php
	while ( $query->have_posts() ) :
		$query->the_post();
		?>
	<div class="ap-artist-card">
		<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'thumbnail' ); ?></a>
		<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p><?php echo wp_trim_words( get_post_meta( get_the_ID(), '_ap_artist_bio', true ), 20 ); ?></p>
	</div>
	<?php endwhile; ?>
	<?php wp_reset_postdata(); ?>
<?php else : ?>
	<p><?php _e( 'No artists found.', 'artpulse' ); ?></p>
<?php endif; ?>
</div>
<?php get_footer(); ?>
