<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

if ( isset( $_POST['ap_refresh_github_release'] ) ) {
	check_admin_referer( 'ap_refresh_github_release' );
	delete_transient( 'ap_latest_github_release' );
	ap_check_for_new_release();
	echo '<div class="notice notice-success"><p>GitHub release data refreshed.</p></div>';
}

$update  = get_option( 'ap_latest_release_info' );
$current = defined( 'ARTPULSE_VERSION' ) ? ARTPULSE_VERSION : '';
?>
<div class="wrap">
	<h2 class="ap-card__title">Update Status</h2>
	<form method="post">
	<?php wp_nonce_field( 'ap_refresh_github_release' ); ?>
	<?php submit_button( 'Refresh Release Info', 'secondary', 'ap_refresh_github_release' ); ?>
	</form>
	<table class="form-table">
	<tr><th>Installed Version</th><td><?php echo esc_html( $current ); ?></td></tr>
	<?php if ( $update ) : ?>
		<tr><th>Latest Version</th><td><strong><?php echo esc_html( $update['version'] ); ?></strong> – <a href="<?php echo esc_url( $update['url'] ); ?>" target="_blank">View on GitHub</a></td></tr>
		<tr><th>Changelog</th><td><pre><?php echo esc_html( $update['notes'] ); ?></pre></td></tr>
	<?php else : ?>
		<tr><th>Latest Version</th><td><em>Up-to-date</em></td></tr>
	<?php endif; ?>
	</table>
</div>

