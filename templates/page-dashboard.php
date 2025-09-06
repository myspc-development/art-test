<?php
/**
 * Template Name: User Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<div class="ap-dashboard-shell">
	<?php get_template_part( 'partials/dashboard-nav' ); ?>
	<main id="ap-view" role="main" aria-live="polite">
		<?php get_template_part( 'partials/dashboard-tiles' ); ?>
	</main>
</div>
<?php get_footer(); ?>
