<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the ArtPulse AI settings page under Settings.
 */
add_action( 'admin_menu', 'artpulse_ai_add_admin_page' );
function artpulse_ai_add_admin_page(): void {
	add_options_page(
		__( 'ArtPulse AI', 'artpulse' ),
		__( 'ArtPulse AI', 'artpulse' ),
		'manage_options',
		'artpulse-ai',
		'artpulse_ai_render_page'
	);
}

/**
 * Register settings for the AI page.
 */
add_action( 'admin_init', 'artpulse_ai_register_settings' );
function artpulse_ai_register_settings(): void {
	register_setting(
		'artpulse_ai_settings',
		'openai_api_key',
		array(
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	register_setting(
		'artpulse_ai_settings',
		'artpulse_tag_prompt',
		array(
			'sanitize_callback' => 'sanitize_textarea_field',
		)
	);
	register_setting(
		'artpulse_ai_settings',
		'artpulse_summary_prompt',
		array(
			'sanitize_callback' => 'sanitize_textarea_field',
		)
	);

	add_settings_section( 'artpulse_ai_main', __( 'OpenAI Settings', 'artpulse' ), '__return_false', 'artpulse-ai' );

	add_settings_field(
		'openai_api_key',
		__( 'OpenAI API Key', 'artpulse' ),
		'artpulse_ai_render_api_key_field',
		'artpulse-ai',
		'artpulse_ai_main'
	);
	add_settings_field(
		'artpulse_tag_prompt',
		__( 'Custom Tag Prompt', 'artpulse' ),
		'artpulse_ai_render_tag_prompt_field',
		'artpulse-ai',
		'artpulse_ai_main'
	);
	add_settings_field(
		'artpulse_summary_prompt',
		__( 'Custom Summary Prompt', 'artpulse' ),
		'artpulse_ai_render_summary_prompt_field',
		'artpulse-ai',
		'artpulse_ai_main'
	);
}

function artpulse_ai_render_api_key_field(): void {
	$value = get_option( 'openai_api_key', '' );
	echo '<input type="password" id="openai_api_key" name="openai_api_key" class="regular-text" value="' . esc_attr( $value ) . '" />';
}

function artpulse_ai_render_tag_prompt_field(): void {
	$value = get_option( 'artpulse_tag_prompt', '' );
	echo '<textarea id="artpulse_tag_prompt" name="artpulse_tag_prompt" rows="4" class="large-text">' . esc_textarea( $value ) . '</textarea>';
}

function artpulse_ai_render_summary_prompt_field(): void {
	$value = get_option( 'artpulse_summary_prompt', '' );
	echo '<textarea id="artpulse_summary_prompt" name="artpulse_summary_prompt" rows="4" class="large-text">' . esc_textarea( $value ) . '</textarea>';
}

/**
 * Output the admin page HTML.
 */
function artpulse_ai_render_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Insufficient permissions', 'artpulse' ) );
	}
	$api_key = get_option( 'openai_api_key', '' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'ArtPulse AI Settings', 'artpulse' ); ?></h1>
		<?php if ( $api_key === '' ) : ?>
			<div class="notice notice-warning">
				<p><?php esc_html_e( 'No OpenAI API key saved.', 'artpulse' ); ?></p>
			</div>
		<?php endif; ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'artpulse_ai_settings' ); ?>
			<?php do_settings_sections( 'artpulse-ai' ); ?>
			<?php submit_button(); ?>
		</form>
		<hr />
		<h2><?php esc_html_e( 'REST Endpoint Tester', 'artpulse' ); ?></h2>
		<textarea id="ap-ai-input" rows="5" class="large-text"></textarea>
		<p>
			<button type="button" class="button" id="ap-ai-tag"><?php esc_html_e( 'Generate Tags', 'artpulse' ); ?></button>
			<button type="button" class="button" id="ap-ai-summary"><?php esc_html_e( 'Generate Bio Summary', 'artpulse' ); ?></button>
		</p>
		<pre id="ap-ai-response" style="background:#fff;border:1px solid #ccd0d4;padding:10px;"></pre>
	</div>
	<?php
}

/**
 * Enqueue admin script for the tester.
 */
add_action( 'admin_enqueue_scripts', 'artpulse_ai_enqueue_scripts' );
function artpulse_ai_enqueue_scripts( string $hook ): void {
	if ( $hook !== 'settings_page_artpulse-ai' ) {
		return;
	}
	$path = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'admin/ai-settings.js';
	wp_enqueue_script(
		'artpulse-ai-admin',
		plugin_dir_url( ARTPULSE_PLUGIN_FILE ) . 'admin/ai-settings.js',
		array( 'jquery' ),
		file_exists( $path ) ? filemtime( $path ) : false,
		true
	);
	wp_localize_script(
		'artpulse-ai-admin',
		'ArtPulseAI',
		array(
			'rest'  => esc_url_raw( rest_url( 'artpulse/v1/' ) ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
		)
	);
}
