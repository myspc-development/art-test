<?php
/**
 * Related Projects section for Salient portfolio Event posts.
 *
 * Outputs linked artist and organization portfolio posts if stored in
 * `_ap_related_artist_ids` and `_ap_related_org_ids` meta. Falls back to the
 * legacy `_ap_related_artists` and `_ap_related_org` keys for compatibility.
 * Developers can filter the heading text and whether artists or organizations
 * display via `ap_related_projects_heading`, `ap_show_related_artists` and
 * `ap_show_related_orgs` filters.
 *
 * Include from `single-portfolio.php` after the main content:
 *
 * ```php
 * locate_template( 'templates/salient/portfolio-related-projects.php', true, true );
 * ```
 *
 * Copy this file to your theme to override the layout.
 *
 * @package ArtPulse
 */

$heading      = $heading ?? apply_filters( 'ap_related_projects_heading', __( 'Related Projects', 'artpulse' ) );
$show_artists = $show_artists ?? apply_filters( 'ap_show_related_artists', true );
$show_orgs    = $show_orgs ?? apply_filters( 'ap_show_related_orgs', true );

$artist_ids = $show_artists ? (array) get_post_meta( get_the_ID(), '_ap_related_artist_ids', true ) : array();
if ( empty( $artist_ids ) ) {
	$artist_ids = $show_artists ? (array) get_post_meta( get_the_ID(), '_ap_related_artists', true ) : array();
}
$artist_ids = array_filter( array_map( 'intval', $artist_ids ) );

$org_ids = $show_orgs ? (array) get_post_meta( get_the_ID(), '_ap_related_org_ids', true ) : array();
if ( empty( $org_ids ) ) {
	$legacy_org = $show_orgs ? (int) get_post_meta( get_the_ID(), '_ap_related_org', true ) : 0;
	if ( $legacy_org ) {
		$org_ids[] = $legacy_org;
	}
}
$org_ids = array_filter( array_map( 'intval', $org_ids ) );

$ids = array_unique( array_merge( $artist_ids, $org_ids ) );
if ( empty( $ids ) ) {
	return;
}

$query = new WP_Query(
	array(
		'post_type'      => 'portfolio',
		'post__in'       => $ids,
		'posts_per_page' => -1,
		'orderby'        => 'post__in',
	)
);

if ( ! $query->have_posts() ) {
	return;
}
?>
<section class="ap-related-projects" aria-labelledby="ap-related-projects-title">
	<h2 id="ap-related-projects-title" class="ap-related-projects-title">
		<?php echo esc_html( $heading ); ?>
	</h2>
	<div class="row portfolio-items">
		<?php
		foreach ( $query->posts as $proj ) :
			$thumb = get_the_post_thumbnail(
				$proj->ID,
				'portfolio-thumb',
				array(
					'loading' => 'lazy',
					'alt'     => get_the_title( $proj->ID ),
				)
			);
			$type  = get_post_meta( $proj->ID, '_ap_source_type', true );
			$label = '';
			if ( 'artpulse_artist' === $type ) {
				$label = __( 'Artist', 'artpulse' );
			} elseif ( 'artpulse_org' === $type ) {
				$label = __( 'Organization', 'artpulse' );
			}
			?>
			<div class="col span_4">
				<div class="nectar-portfolio-item">
					<a href="<?php echo esc_url( get_permalink( $proj ) ); ?>">
						<?php
						if ( $thumb ) {
							echo $thumb; }
						?>
						<h3><?php echo esc_html( get_the_title( $proj ) ); ?></h3>
					</a>
					<?php if ( $label ) : ?>
						<span class="ap-project-label"><?php echo esc_html( $label ); ?></span>
					<?php endif; ?>
					<a class="nectar-button small" href="<?php echo esc_url( get_permalink( $proj ) ); ?>">
						<?php esc_html_e( 'View Profile', 'artpulse' ); ?>
					</a>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
<?php
wp_reset_postdata();
