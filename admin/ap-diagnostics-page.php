<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

// Gather system info
$info = array(
	'WordPress Version' => get_bloginfo( 'version' ),
	'Site URL'          => site_url(),
	'PHP Version'       => phpversion(),
	'Memory Limit'      => ini_get( 'memory_limit' ),
	'REST Enabled'      => rest_url() !== '' ? '✅' : '❌',
	'HTTPS Active'      => is_ssl() ? '✅' : '❌',
);

$cron_disabled   = defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;
$info['WP Cron'] = $cron_disabled ? '❌ Disabled' : '✅ Enabled';

$active_plugins = array_map(
	function ( $p ) {
		$data = get_plugin_data( WP_PLUGIN_DIR . '/' . $p );
		return $data['Name'] . ' ' . $data['Version'];
	},
	(array) get_option( 'active_plugins', array() )
);

$user_caps = array_keys( (array) wp_get_current_user()->allcaps );
$repaired  = isset( $_GET['repaired'] );
?>
<div class="wrap">
	<h1><?php esc_html_e( 'ArtPulse Diagnostics', 'artpulse' ); ?></h1>
	<h2 class="ap-card__title"><?php esc_html_e( 'System Information', 'artpulse' ); ?></h2>
	<table class="form-table">
		<tbody>
		<?php foreach ( $info as $label => $value ) : ?>
			<tr>
				<th scope="row"><?php echo esc_html( $label ); ?></th>
				<td><?php echo esc_html( $value ); ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<h2 class="ap-card__title"><?php esc_html_e( 'AJAX Test', 'artpulse' ); ?></h2>
	<p id="ap-ajax-result"></p>
	<p>
		<button id="ap-ajax-test" class="button button-secondary"><?php esc_html_e( 'Run AJAX Test', 'artpulse' ); ?></button>
	</p>

	<h2 class="ap-card__title"><?php esc_html_e( 'Capabilities', 'artpulse' ); ?></h2>
	<ul>
		<?php foreach ( $user_caps as $cap ) : ?>
			<li><?php echo esc_html( $cap ); ?></li>
		<?php endforeach; ?>
	</ul>

	<h2 class="ap-card__title"><?php esc_html_e( 'Active Plugins', 'artpulse' ); ?></h2>
	<ul>
		<?php foreach ( $active_plugins as $plugin ) : ?>
			<li><?php echo esc_html( $plugin ); ?></li>
		<?php endforeach; ?>
	</ul>

	<h2 class="ap-card__title"><?php esc_html_e( 'Maintenance', 'artpulse' ); ?></h2>
	<?php if ( $repaired ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Database tables repaired.', 'artpulse' ); ?></p></div>
	<?php endif; ?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'ap_repair_tables' ); ?>
		<input type="hidden" name="action" value="ap_repair_tables">
		<?php submit_button( __( 'Repair Tables', 'artpulse' ) ); ?>
	</form>
</div>
<script type="text/javascript">
(function($){
	$('#ap-ajax-test').on('click', function(e){
		e.preventDefault();
		$('#ap-ajax-result').text('<?php echo esc_js( __( 'Running...', 'artpulse' ) ); ?>');
		$.post(ajaxurl, {
			action: 'ap_ajax_test',
			nonce: '<?php echo wp_create_nonce( 'ap_diagnostics_test' ); ?>'
		}).done(function(resp){
			if(resp.success){
				$('#ap-ajax-result').text(resp.data.message);
			} else {
				$('#ap-ajax-result').text('Error');
			}
		}).fail(function(){
			$('#ap-ajax-result').text('Request failed');
		});
	});
})(jQuery);
</script>
