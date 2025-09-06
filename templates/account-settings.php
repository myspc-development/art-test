<div class="ap-account-settings">
	<h2 class="ap-card__title"><?php esc_html_e( 'Account Settings', 'artpulse' ); ?></h2>
	<form id="ap-notification-prefs" class="ap-form-container" action="<?php echo esc_url( rest_url( 'artpulse/v1/user-preferences' ) ); ?>">
		<label>
			<input type="checkbox" name="email" <?php checked( $email ); ?>>
			<?php esc_html_e( 'Email Notifications', 'artpulse' ); ?>
		</label>
		<label>
			<input type="checkbox" name="push" <?php checked( $push ); ?>>
			<?php esc_html_e( 'Push Notifications', 'artpulse' ); ?>
		</label>
		<label>
			<input type="checkbox" name="sms" <?php checked( $sms ); ?>>
			<?php esc_html_e( 'SMS Notifications', 'artpulse' ); ?>
		</label>
		<p>
			<label for="ap_digest_frequency" class="ap-form-label"><?php esc_html_e( 'Digest Frequency', 'artpulse' ); ?></label><br>
			<select id="ap_digest_frequency" name="digest_frequency" class="ap-input">
				<option value="none" <?php selected( $digest_frequency, 'none' ); ?>><?php esc_html_e( 'None', 'artpulse' ); ?></option>
				<option value="weekly" <?php selected( $digest_frequency, 'weekly' ); ?>><?php esc_html_e( 'Weekly', 'artpulse' ); ?></option>
				<option value="monthly" <?php selected( $digest_frequency, 'monthly' ); ?>><?php esc_html_e( 'Monthly', 'artpulse' ); ?></option>
			</select>
		</p>
		<p>
			<label for="ap_digest_topics" class="ap-form-label"><?php esc_html_e( 'Digest Topics', 'artpulse' ); ?></label><br>
			<input type="text" id="ap_digest_topics" name="digest_topics" class="ap-input" value="<?php echo esc_attr( $digest_topics ); ?>" placeholder="painting, sculpture">
		</p>
		<?php wp_nonce_field( 'ap_notification_prefs', 'ap_notification_nonce' ); ?>
		<button type="submit" class="ap-form-button nectar-button"><?php esc_html_e( 'Save', 'artpulse' ); ?></button>
	</form>
	<div id="ap-notification-status" class="ap-form-messages" role="status" aria-live="polite"></div>
</div>
