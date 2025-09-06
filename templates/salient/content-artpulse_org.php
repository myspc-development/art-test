<?php
if ( ! defined( 'WP_ENV_FOR_TESTS' ) ) {
	\get_header();
}

// During tests various template helpers are defined in a namespaced context
// so we need to explicitly call them when the test flag is set. When running
// under WordPress the normal global functions are used instead.
if ( defined( 'WP_ENV_FOR_TESTS' ) ) {
	$have_posts         = '\\ArtPulse\\Frontend\\Tests\\have_posts';
	$the_post           = '\\ArtPulse\\Frontend\\Tests\\the_post';
	$the_title          = '\\ArtPulse\\Frontend\\Tests\\the_title';
	$the_content        = '\\ArtPulse\\Frontend\\Tests\\the_content';
	$the_post_thumbnail = '\\ArtPulse\\Frontend\\Tests\\the_post_thumbnail';
	$get_the_ID         = '\\ArtPulse\\Frontend\\Tests\\get_the_ID';
	$get_post_meta      = static function ( $id, $key, $single = false ) {
		return \ArtPulse\Tests\Stubs\MockStorage::$post_meta[ $key ] ?? '';
	};
} else {
	$have_posts         = 'have_posts';
	$the_post           = 'the_post';
	$the_title          = 'the_title';
	$the_content        = 'the_content';
	$the_post_thumbnail = 'the_post_thumbnail';
	$get_the_ID         = 'get_the_ID';
	$get_post_meta      = 'get_post_meta';
}
?>
<?php
if ( $have_posts() ) {
	$the_post();
	?>
	<div class="nectar-portfolio-single-media">
	<?php $the_post_thumbnail( 'full', array( 'class' => 'img-responsive' ) ); ?>
	</div>
	<h1 class="entry-title"><?php $the_title(); ?></h1>
	<?php if ( ! defined( 'WP_ENV_FOR_TESTS' ) ) : ?>
		<?php echo \ArtPulse\Frontend\ap_render_favorite_button( $get_the_ID(), 'artpulse_org' ); ?>
	<?php endif; ?>
	<div class="entry-content"><?php $the_content(); ?></div>
	<?php
	$address = $get_post_meta( $get_the_ID(), 'ead_org_street_address', true );
	$website = $get_post_meta( $get_the_ID(), 'ead_org_website_url', true );
	if ( $address || $website ) :
		?>
	<ul class="portfolio-meta">
		<?php if ( $address ) : ?>
		<li><strong><?php \esc_html_e( 'Address:', 'artpulse' ); ?></strong> <?php echo \esc_html( $address ); ?></li>
		<?php endif; ?>
		<?php if ( $website ) : ?>
		<li><strong><?php \esc_html_e( 'Website:', 'artpulse' ); ?></strong>
			<a href="<?php echo \esc_url( $website ); ?>" target="_blank"><?php echo \esc_html( $website ); ?></a>
		</li>
		<?php endif; ?>
	</ul>
	<?php endif; ?>

	<?php
	$days  = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
	$hours = array();
	foreach ( $days as $day ) {
		$start  = $get_post_meta( $get_the_ID(), "ead_org_{$day}_start_time", true );
		$end    = $get_post_meta( $get_the_ID(), "ead_org_{$day}_end_time", true );
		$closed = $get_post_meta( $get_the_ID(), "ead_org_{$day}_closed", true );
		if ( $start || $end || $closed ) {
			$hours[ $day ] = array(
				'start'  => $start,
				'end'    => $end,
				'closed' => $closed,
			);
		}
	}
	if ( ! empty( $hours ) ) :
		?>
	<h2 class="ap-card__title"><?php \esc_html_e( 'Opening Hours', 'artpulse' ); ?></h2>
	<ul class="portfolio-meta opening-hours">
		<?php foreach ( $hours as $day => $vals ) : ?>
		<li><strong><?php echo \esc_html( \ucfirst( $day ) . ':' ); ?></strong>
			<?php echo $vals['closed'] ? \esc_html__( 'Closed', 'artpulse' ) : \esc_html( \trim( $vals['start'] . ' - ' . $vals['end'] ) ); ?>
		</li>
		<?php endforeach; ?>
	</ul>
	<?php endif; ?>

<?php } ?>
