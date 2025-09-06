<?php
/**
 * Template part for Salient portfolio-style archives.
 */
?>
<div class="row portfolio-archive">
<?php
if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'col-md-12 ap-archive-item' ); ?>>
			<?php if ( has_post_thumbnail() ) : ?>
			<div class="ap-thumbnail">
				<a href="<?php the_permalink(); ?>">
					<?php the_post_thumbnail( 'medium', array( 'class' => 'img-responsive' ) ); ?>
				</a>
			</div>
			<?php endif; ?>
			<h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<div class="entry-summary">
				<?php the_excerpt(); ?>
			</div>
			<?php
			$type = get_post_type();
			echo '<ul class="portfolio-meta">';
			switch ( $type ) {
				case 'artpulse_org':
					$address = get_post_meta( get_the_ID(), 'ead_org_street_address', true );
					$website = get_post_meta( get_the_ID(), 'ead_org_website_url', true );
					if ( $address ) {
						echo '<li><strong>' . esc_html__( 'Address:', 'artpulse' ) . '</strong> ' . esc_html( $address ) . '</li>';
					}
					if ( $website ) {
						echo '<li><strong>' . esc_html__( 'Website:', 'artpulse' ) . '</strong> <a href="' . esc_url( $website ) . '" target="_blank">' . esc_html( $website ) . '</a></li>';
					}
					break;
				case 'artpulse_artist':
					$bio = get_post_meta( get_the_ID(), '_ap_artist_bio', true );
					$org = get_post_meta( get_the_ID(), '_ap_artist_org', true );
					if ( $bio ) {
						echo '<li><strong>' . esc_html__( 'Biography:', 'artpulse' ) . '</strong> ' . wp_kses_post( $bio ) . '</li>';
					}
					if ( $org ) {
						echo '<li><strong>' . esc_html__( 'Organization ID:', 'artpulse' ) . '</strong> ' . esc_html( $org ) . '</li>';
					}
					break;
				case 'artpulse_artwork':
					$medium     = get_post_meta( get_the_ID(), '_ap_artwork_medium', true );
					$dimensions = get_post_meta( get_the_ID(), '_ap_artwork_dimensions', true );
					$materials  = get_post_meta( get_the_ID(), '_ap_artwork_materials', true );
					if ( $medium ) {
						echo '<li><strong>' . esc_html__( 'Medium:', 'artpulse' ) . '</strong> ' . esc_html( $medium ) . '</li>';
					}
					if ( $dimensions ) {
						echo '<li><strong>' . esc_html__( 'Dimensions:', 'artpulse' ) . '</strong> ' . esc_html( $dimensions ) . '</li>';
					}
					if ( $materials ) {
						echo '<li><strong>' . esc_html__( 'Materials:', 'artpulse' ) . '</strong> ' . esc_html( $materials ) . '</li>';
					}
					break;
				case 'artpulse_event':
					$date     = get_post_meta( get_the_ID(), '_ap_event_date', true );
					$location = get_post_meta( get_the_ID(), '_ap_event_location', true );
					echo '<li><strong>' . esc_html__( 'Date:', 'artpulse' ) . '</strong> ' . esc_html( $date ?: __( 'Not specified', 'artpulse' ) ) . '</li>';
					echo '<li><strong>' . esc_html__( 'Location:', 'artpulse' ) . '</strong> ' . esc_html( $location ?: __( 'Not specified', 'artpulse' ) ) . '</li>';
					break;
			}
			echo '</ul>';
			?>
		</article>
		<?php
	endwhile;
else :
	?>
	<p><?php esc_html_e( 'No posts found.', 'artpulse' ); ?></p>
<?php endif; ?>
</div>
