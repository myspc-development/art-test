<?php
use ArtPulse\Core\Plugin;
?>
<div class="ap-payouts">
	<h2 class="ap-card__title"><?php esc_html_e( 'Payouts', 'artpulse' ); ?></h2>
	<div id="ap-payout-summary">
		<p id="ap-payout-balance"></p>
	</div>
	<h3><?php esc_html_e( 'Update Method', 'artpulse' ); ?></h3>
	<form id="ap-payout-settings" class="ap-form-container">
		<label for="ap-payout-method"><?php esc_html_e( 'Method', 'artpulse' ); ?></label>
		<?php $method = get_user_meta( get_current_user_id(), 'ap_payout_method', true ); ?>
		<select name="method" id="ap-payout-method">
			<option value="paypal" <?php selected( $method, 'paypal' ); ?>><?php esc_html_e( 'PayPal', 'artpulse' ); ?></option>
			<option value="bank" <?php selected( $method, 'bank' ); ?>><?php esc_html_e( 'Bank Transfer', 'artpulse' ); ?></option>
		</select>
		<button type="submit" class="ap-form-button nectar-button"><?php esc_html_e( 'Save', 'artpulse' ); ?></button>
	</form>
	<div id="ap-payout-status" class="ap-form-messages" role="status" aria-live="polite"></div>
	<h3><?php esc_html_e( 'History', 'artpulse' ); ?></h3>
	<div id="ap-payout-history"></div>
</div>
