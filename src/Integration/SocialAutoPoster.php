<?php
namespace ArtPulse\Integration;

use ArtPulse\Admin\SettingsRegistry;
use ArtPulse\Support\WpAdminFns;

class SocialAutoPoster {

	private const OPTION_KEY = 'ap_social_auto_post_settings';
	private const NETWORKS   = array( 'facebook', 'instagram', 'twitter', 'pinterest' );
	private const POST_TYPES = array(
		'event'        => 'artpulse_event',
		'artwork'      => 'artpulse_artwork',
		'organization' => 'artpulse_org',
		'artist'       => 'artpulse_artist',
		'news'         => 'post',
		'portfolio'    => 'artpulse_portfolio',
	);

	public static function register(): void {
		self::register_tab();
		add_action( 'admin_init', array( self::class, 'register_option' ) );
		add_action( 'init', array( self::class, 'register_publish_hooks' ) );
	}

	private static function register_tab(): void {
		SettingsRegistry::register_tab( 'social_auto', __( 'Social Auto-Posting', 'artpulse' ) );
	}

	public static function register_option(): void {
		register_setting(
			'ap_social_auto_post_settings_group',
			self::OPTION_KEY,
			array( 'sanitize_callback' => array( self::class, 'sanitize' ) )
		);
	}

	public static function sanitize( $input ): array {
		$out = array();
		foreach ( self::NETWORKS as $network ) {
			$data            = $input[ $network ] ?? array();
			$out[ $network ] = array(
				'enabled' => ! empty( $data['enabled'] ) ? 1 : 0,
				'token'   => sanitize_text_field( $data['token'] ?? '' ),
			);
			if ( $network === 'facebook' ) {
				$out[ $network ]['page_id'] = sanitize_text_field( $data['page_id'] ?? '' );
			}
			if ( $network === 'pinterest' ) {
				$out[ $network ]['board'] = sanitize_text_field( $data['board'] ?? '' );
			}
		}
		$out['post_types'] = array();
		foreach ( self::POST_TYPES as $key => $pt ) {
			$out['post_types'][ $key ] = ! empty( $input['post_types'][ $key ] ) ? 1 : 0;
		}
		return $out;
	}

	public static function render_settings(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Insufficient permissions', 'artpulse' ) );
		}
		$opts = get_option( self::OPTION_KEY, array() );
		?>
		<form method="post" action="options.php" class="ap-settings-form">
			<?php WpAdminFns::settings_fields( 'ap_social_auto_post_settings_group' ); ?>
			<h2 class="ap-card__title"><?php esc_html_e( 'Social Auto-Posting', 'artpulse' ); ?></h2>
			<?php foreach ( self::NETWORKS as $network ) : ?>
				<fieldset class="ap-fieldset">
					<legend><?php echo esc_html( ucfirst( $network ) ); ?></legend>
					<label>
						<input type="checkbox" name="<?php echo self::OPTION_KEY; ?>[<?php echo esc_attr( $network ); ?>][enabled]" <?php checked( $opts[ $network ]['enabled'] ?? false ); ?> />
						<?php esc_html_e( 'Enable', 'artpulse' ); ?>
					</label><br>
					<label>
						<?php esc_html_e( 'API Key/Token:', 'artpulse' ); ?>
						<input type="text" name="<?php echo self::OPTION_KEY; ?>[<?php echo esc_attr( $network ); ?>][token]" value="<?php echo esc_attr( $opts[ $network ]['token'] ?? '' ); ?>" class="regular-text" />
					</label><br>
					<?php if ( $network === 'facebook' ) : ?>
						<label>
							<?php esc_html_e( 'Page ID:', 'artpulse' ); ?>
							<input type="text" name="<?php echo self::OPTION_KEY; ?>[facebook][page_id]" value="<?php echo esc_attr( $opts['facebook']['page_id'] ?? '' ); ?>" class="regular-text" />
						</label>
					<?php endif; ?>
					<?php if ( $network === 'pinterest' ) : ?>
						<label>
							<?php esc_html_e( 'Board Name/ID:', 'artpulse' ); ?>
							<input type="text" name="<?php echo self::OPTION_KEY; ?>[pinterest][board]" value="<?php echo esc_attr( $opts['pinterest']['board'] ?? '' ); ?>" class="regular-text" />
						</label>
					<?php endif; ?>
				</fieldset>
				<hr />
			<?php endforeach; ?>
			<h3><?php esc_html_e( 'Enable auto-posting for these post types:', 'artpulse' ); ?></h3>
			<?php foreach ( self::POST_TYPES as $key => $_pt ) : ?>
				<label>
					<input type="checkbox" name="<?php echo self::OPTION_KEY; ?>[post_types][<?php echo esc_attr( $key ); ?>]" <?php checked( $opts['post_types'][ $key ] ?? false ); ?> />
					<?php echo esc_html( ucfirst( $key ) ); ?>
				</label><br>
			<?php endforeach; ?>
			<?php WpAdminFns::submit_button(); ?>
		</form>
		<?php
	}

	public static function register_publish_hooks(): void {
		$opts = self::get_options();
		foreach ( self::POST_TYPES as $key => $post_type ) {
			if ( ! empty( $opts['post_types'][ $key ] ) ) {
				add_action( "publish_{$post_type}", array( self::class, 'handle_publish' ), 10, 2 );
			}
		}
	}

	private static function get_options(): array {
		$opts = get_option( self::OPTION_KEY, array() );
		return is_array( $opts ) ? $opts : array();
	}

	public static function handle_publish( int $post_id, \WP_Post $post ): void {
		$opts = self::get_options();
		foreach ( self::NETWORKS as $network ) {
			$cfg = $opts[ $network ] ?? array();
			if ( empty( $cfg['enabled'] ) || empty( $cfg['token'] ) ) {
				continue;
			}
			$msg = wp_strip_all_tags( $post->post_title ) . ' ' . get_permalink( $post_id );
			switch ( $network ) {
				case 'facebook':
					self::post_facebook( $msg, $cfg );
					break;
				case 'instagram':
					self::post_instagram( $msg, $cfg );
					break;
				case 'twitter':
					self::post_twitter( $msg, $cfg );
					break;
				case 'pinterest':
					self::post_pinterest( $msg, $cfg );
					break;
			}
		}
	}

	private static function post_facebook( string $msg, array $cfg ): void {
		if ( empty( $cfg['page_id'] ) ) {
			return;
		}
		$url = 'https://graph.facebook.com/' . rawurlencode( $cfg['page_id'] ) . '/feed';
		wp_remote_post(
			$url,
			array(
				'body'    => array(
					'message'      => $msg,
					'access_token' => $cfg['token'],
				),
				'timeout' => 5,
			)
		);
	}

	private static function post_instagram( string $msg, array $cfg ): void {
		$url = 'https://graph.facebook.com/me/media';
		wp_remote_post(
			$url,
			array(
				'body'    => array(
					'caption'      => $msg,
					'access_token' => $cfg['token'],
				),
				'timeout' => 5,
			)
		);
	}

	private static function post_twitter( string $msg, array $cfg ): void {
		$url = 'https://api.twitter.com/2/tweets';
		wp_remote_post(
			$url,
			array(
				'headers' => array( 'Authorization' => 'Bearer ' . $cfg['token'] ),
				'body'    => wp_json_encode( array( 'text' => $msg ) ),
				'timeout' => 5,
			)
		);
	}

	private static function post_pinterest( string $msg, array $cfg ): void {
		if ( empty( $cfg['board'] ) ) {
			return;
		}
		$url = 'https://api.pinterest.com/v5/pins';
		wp_remote_post(
			$url,
			array(
				'headers' => array( 'Authorization' => 'Bearer ' . $cfg['token'] ),
				'body'    => array(
					'board_id' => $cfg['board'],
					'title'    => $msg,
					'link'     => home_url(),
				),
				'timeout' => 5,
			)
		);
	}
}
