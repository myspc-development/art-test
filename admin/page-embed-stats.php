<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }
global $wpdb;
$table = $wpdb->prefix . 'ap_embed_logs';
$rows  = $wpdb->get_results( "SELECT widget_id, action, COUNT(*) as total FROM $table GROUP BY widget_id, action", ARRAY_A );
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Embed Stats', 'artpulse' ); ?></h1>
	<?php if ( empty( $rows ) ) : ?>
	<p><?php esc_html_e( 'No stats recorded yet.', 'artpulse' ); ?></p>
	<?php else : ?>
	<table class="widefat"><thead><tr><th><?php esc_html_e( 'Widget ID', 'artpulse' ); ?></th><th><?php esc_html_e( 'Action', 'artpulse' ); ?></th><th><?php esc_html_e( 'Count', 'artpulse' ); ?></th></tr></thead><tbody>
		<?php foreach ( $rows as $r ) : ?>
	<tr><td><?php echo esc_html( $r['widget_id'] ); ?></td><td><?php echo esc_html( $r['action'] ); ?></td><td><?php echo esc_html( $r['total'] ); ?></td></tr>
	<?php endforeach; ?>
	</tbody></table>
	<?php endif; ?>
</div>
