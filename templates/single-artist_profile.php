<?php
/** Template for single Artist Profile */
get_header();
if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		$author_id = get_the_author_meta( 'ID' );
		$bio       = get_user_meta( $author_id, 'description', true );
		$twitter   = get_user_meta( $author_id, 'ap_social_twitter', true );
		$instagram = get_user_meta( $author_id, 'ap_social_instagram', true );
		$website   = get_user_meta( $author_id, 'ap_social_website', true );
		$items     = get_posts(
			array(
				'post_type'      => 'artpulse_portfolio',
				'meta_key'       => '_ap_artist_profile',
				'meta_value'     => get_the_ID(),
				'posts_per_page' => -1,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			)
		);
		$images    = array();
		$featured  = 0;
		foreach ( $items as $item ) {
			$img = (int) get_post_meta( $item->ID, 'portfolio_image', true );
			if ( $img ) {
				$images[] = $img;
				if ( get_post_meta( $item->ID, 'portfolio_featured', true ) ) {
					$featured = $img;
				}
			}
		}
		?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'ap-artist-profile' ); ?>>
	<header class="ap-artist-header">
	<h1 class="ap-artist-title"><?php the_title(); ?></h1>
	</header>
		<?php if ( $bio ) : ?>
	<div class="ap-artist-bio"><?php echo wpautop( esc_html( $bio ) ); ?></div>
	<?php endif; ?>
	<ul class="ap-artist-social">
		<?php
		if ( $twitter ) :
			?>
			<li><a href="<?php echo esc_url( $twitter ); ?>">Twitter</a></li><?php endif; ?>
		<?php
		if ( $instagram ) :
			?>
			<li><a href="<?php echo esc_url( $instagram ); ?>">Instagram</a></li><?php endif; ?>
		<?php
		if ( $website ) :
			?>
			<li><a href="<?php echo esc_url( $website ); ?>">Website</a></li><?php endif; ?>
	</ul>
		<?php if ( $images ) : ?>
	<div class="ap-artist-gallery">
			<?php foreach ( $images as $img_id ) : ?>
		<figure class="ap-artist-gallery-item<?php echo $img_id == $featured ? ' is-featured' : ''; ?>">
				<?php echo wp_kses_post( wp_get_attachment_image( $img_id, 'large', false, array( 'loading' => 'lazy' ) ) ); ?>
		</figure>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
</article>
		<?php
		$graph   = array();
		$graph[] = array(
			'@type'       => 'Person',
			'name'        => get_the_title(),
			'url'         => get_permalink(),
			'description' => $bio,
			'sameAs'      => array_values( array_filter( array( $twitter, $instagram, $website ) ) ),
		);
		if ( $images ) {
			foreach ( $images as $img_id ) {
				$graph[] = array(
					'@type' => 'CreativeWork',
					'name'  => get_post_meta( $img_id, '_wp_attachment_image_alt', true ),
					'image' => wp_get_attachment_url( $img_id ),
				);
			}
		}
		echo '<script type="application/ld+json">' . wp_json_encode(
			array(
				'@context' => 'https://schema.org',
				'@graph'   => $graph,
			)
		) . '</script>';
	endwhile;
endif;
get_footer();
