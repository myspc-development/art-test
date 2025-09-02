<?php
namespace ArtPulse\Frontend;

class EventChatShortcode {

	public static function register(): void {
		\ArtPulse\Core\ShortcodeRegistry::register( 'ap_event_chat', 'Event Chat', array( self::class, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_styles' ) );
	}

	public static function enqueue_styles(): void {
		if ( function_exists( 'ap_enqueue_global_styles' ) ) {
			ap_enqueue_global_styles();
		}
		wp_enqueue_style(
			'ap-chat-style',
			plugin_dir_url( ARTPULSE_PLUGIN_FILE ) . 'assets/css/ap-chat.css',
			array(),
			filemtime( plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'assets/css/ap-chat.css' )
		);
	}

	public static function render( $atts = array() ): string {
		$atts = shortcode_atts(
			array(
				'id' => get_the_ID(),
			),
			$atts,
			'ap_event_chat'
		);

		$event_id = intval( $atts['id'] );
		if ( ! $event_id ) {
			return '';
		}

               $logged_in = \is_user_logged_in();
               $can_post  = false;

               if ( $logged_in ) {
                       $req = new \WP_REST_Request( 'POST', '/' );
                       $req->set_param( 'id', $event_id );
                       $can_post = \ArtPulse\Community\EventChatController::can_post( $req );
               }

		ob_start();
		?>
		<div class="ap-event-chat" data-event-id="<?php echo esc_attr( $event_id ); ?>" data-can-post="<?php echo $can_post ? '1' : '0'; ?>">
                        <ul class="ap-chat-list" role="status" aria-live="polite"></ul>
                        <?php if ( $logged_in ) : ?>
                                <?php if ( $can_post ) : ?>
                                        <form class="ap-chat-form">
                                                <input type="text" name="content" required>
                                                <button type="submit">Send</button>
                                        </form>
                                <?php else : ?>
                               <p><?php esc_html_e( 'Only attendees can post messages.', 'artpulse' ); ?></p>
                                <?php endif; ?>
                        <?php else : ?>
                                <p><?php esc_html_e( 'Please log in to chat.', 'artpulse' ); ?></p>
                        <?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}
