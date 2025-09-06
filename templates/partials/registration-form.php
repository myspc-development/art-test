<form class="submission-form" id="ap-register-form" method="post" action="<?php echo esc_url( add_query_arg( 'ap-register', '1' ) ); ?>">
	<?php wp_nonce_field( 'ap_register_form', 'ap_register_nonce' ); ?>
	<fieldset class="form-section">
	<legend>Contact Info</legend>
	<label for="ap_reg_username">Username</label>
	<input id="ap_reg_username" name="username" type="text">
	<label for="ap_reg_email">Email</label>
	<input id="ap_reg_email" name="email" type="email">
	<label for="ap_reg_pass">Password</label>
	<input id="ap_reg_pass" name="password" type="password">
	<label for="ap_reg_confirm">Confirm Password</label>
	<input id="ap_reg_confirm" name="password_confirm" type="password">
	</fieldset>
	<fieldset class="form-section">
	<legend>Details</legend>
	<label for="ap_reg_bio">Bio</label>
	<textarea id="ap_reg_bio" name="description"></textarea>
	</fieldset>
	<fieldset class="form-section">
	<legend>Location</legend>
	<label for="ap_country">Country</label>
	<input id="ap_country" class="ap-address-country" name="ap_country" type="text">

	<label for="ap_state">State/Province</label>
	<input id="ap_state" class="ap-address-state" name="ap_state" type="text">

	<label for="ap_city">City</label>
	<input id="ap_city" class="ap-address-city" name="ap_city" type="text">

	<label for="ap_suburb">Suburb</label>
	<input id="ap_suburb" class="ap-google-autocomplete" name="ap_suburb" type="text">

	<label for="ap_street">Street Address</label>
	<input id="ap_street" class="ap-google-autocomplete" name="ap_street" type="text">

	<input type="hidden" id="ap_address_components" name="address_components" value="">
	</fieldset>
	<button type="submit" class="button-primary" name="ap_register_submit">Submit</button>
</form>
