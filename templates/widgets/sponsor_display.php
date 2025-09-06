<?php if ( ! defined( 'ABSPATH' ) ) {
	exit; } ?>
<?php
use ArtPulse\Core\DashboardWidgetRegistry;

if ( class_exists( DashboardWidgetRegistry::class ) ) {
	DashboardWidgetRegistry::init();
}

$id    = 'sponsor_display';
$def   = function_exists( '\\ArtPulse\\Core\\DashboardWidgetRegistry::getById' )
	? DashboardWidgetRegistry::getById( $id )
	: null;
$title = $def['label'] ?? __( 'Sponsor Display', 'artpulse' );

$api_root = esc_url_raw( rest_url() );
$nonce    = wp_create_nonce( 'wp_rest' );
$org_id   = (int) apply_filters( 'ap_current_org_id', 0 );
if ( ! $org_id ) {
	$org_id = (int) get_user_meta( get_current_user_id(), 'ap_selected_org_id', true );
}

$heading_id = sanitize_title( $id ) . '-heading-' . uniqid();
?>
<section role="region" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>"
	data-widget="<?php echo esc_attr( $id ); ?>"
	data-widget-id="<?php echo esc_attr( $id ); ?>"
	data-api-root="<?php echo esc_attr( $api_root ); ?>"
	data-nonce="<?php echo esc_attr( $nonce ); ?>"
	data-org-id="<?php echo esc_attr( $org_id ); ?>"
	class="ap-widget ap-<?php echo esc_attr( $id ); ?>">

	<h2 id="<?php echo esc_attr( $heading_id ); ?>"><?php echo esc_html( $title ); ?></h2>

	<p class="ap-widget__placeholder">
	<?php echo esc_html__( 'This widget will load dynamic data when JS initializes.', 'artpulse' ); ?>
	</p>

	<?php if ( ! $org_id ) : ?>
	<p class="notice notice-warning">
		<?php echo esc_html__( 'No organization context detected (orgId=0). Some features may be disabled in preview.', 'artpulse' ); ?>
	</p>
	<?php endif; ?>

</section>
