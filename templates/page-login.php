<?php
/* Template Name: ArtPulse Login */
get_header();
?>
<div class="ap-login-page">
	<?php
	$output = do_shortcode( '[ap_login]' );
	echo wp_kses_post( $output );
	?>
</div>
<?php
get_footer();
