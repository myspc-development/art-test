<h2 class="ap-card__title">Plugin Updates</h2>
<p>Current Version: <strong><?php echo esc_html( ARTPULSE_VERSION ); ?></strong></p>

<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
	<?php wp_nonce_field( 'ap_check_updates' ); ?>
	<input type="hidden" name="action" value="ap_check_updates">
	<button type="submit" class="button">Check for Updates</button>
</form>

<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
	<?php wp_nonce_field( 'ap_run_update' ); ?>
	<input type="hidden" name="action" value="ap_run_update">
	<button type="submit" class="button button-primary">Run Update</button>
</form>

<?php if ( $log = get_option( 'ap_update_log' ) ) : ?>
	<h3>Update Log</h3>
	<pre><?php echo esc_html( $log ); ?></pre>
<?php endif; ?>

<hr>
<h2 class="ap-card__title">Advanced Update Diagnostics</h2>
<div id="ap-update-output">Loading…</div>
