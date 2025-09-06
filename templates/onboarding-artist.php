<?php
/**
 * Artist onboarding first step.
 */
?>
<div class="ap-onboarding-wizard">
	<h2 class="ap-card__title"><?php esc_html_e( 'Welcome to ArtPulse!', 'artpulse' ); ?></h2>
	<p><?php esc_html_e( 'Let\'s complete your profile to get started.', 'artpulse' ); ?></p>
	<div class="ap-onboarding-actions">
		<button id="ap-onboarding-next" data-step="profile" class="ap-form-button"><?php esc_html_e( 'Next', 'artpulse' ); ?></button>
		<button id="ap-onboarding-skip" class="ap-form-button"><?php esc_html_e( 'Skip', 'artpulse' ); ?></button>
	</div>
</div>
