<?php
namespace ArtPulse\Frontend;

class SubmitArtistForm {
	public static function register(): void {
		\ArtPulse\Core\ShortcodeRegistry::register( 'ap_submit_artist', 'Submit Artist', array( self::class, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_styles' ) );
	}

	public static function enqueue_styles(): void {
		if ( function_exists( 'ap_enqueue_global_styles' ) ) {
			ap_enqueue_global_styles();
		}
	}

	public static function render(): string {
		if ( ! defined( 'ARTPULSE_PLUGIN_FILE' ) ) {
			return '';
		}

		wp_enqueue_script( 'ap-artist-submission-js' );

		ob_start();
		?>
		<form class="submission-form ap-artist-submission-form ap-form-container" enctype="multipart/form-data" data-post-type="artpulse_event">
			<fieldset class="form-section">
				<legend>Contact Info</legend>
				<label for="ap-artist-title"><?php esc_html_e( 'Artist Name', 'artpulse' ); ?></label>
				<input id="ap-artist-title" type="text" name="title" required>

				<label for="ap-artist-org"><?php esc_html_e( 'Organization ID', 'artpulse' ); ?></label>
				<input id="ap-artist-org" type="number" name="artist_org">
			</fieldset>

			<fieldset class="form-section">
				<legend>Details</legend>
				<label for="ap-artist-bio"><?php esc_html_e( 'Biography', 'artpulse' ); ?></label>
				<textarea id="ap-artist-bio" name="artist_bio"></textarea>

				<label><?php esc_html_e( 'Images (max 5)', 'artpulse' ); ?></label>
				<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
					<input id="ap-artist-image<?php echo $i; ?>" class="ap-artist-image" type="file" name="image_<?php echo $i; ?>" accept="image/*">
				<?php endfor; ?>
			</fieldset>

			<button type="submit" class="button-primary"><?php esc_html_e( 'Submit', 'artpulse' ); ?></button>
		</form>
		<div class="ap-form-messages" role="status" aria-live="polite"></div>
		<?php
		return ob_get_clean();
	}
}
