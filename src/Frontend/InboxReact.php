<?php
namespace ArtPulse\Frontend;

use ArtPulse\Community\DirectMessages;

class InboxReact {
	public static function register(): void {
		\ArtPulse\Core\ShortcodeRegistry::register( 'ap_inbox_app', 'Inbox App', array( self::class, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue' ) );
	}

	public static function enqueue(): void {
		$post = get_post();
		if ( $post instanceof \WP_Post && has_shortcode( $post->post_content, 'ap_inbox_app' ) ) {
			wp_enqueue_script(
				'ap-inbox-app',
				plugins_url( 'assets/js/inbox-app.js', ARTPULSE_PLUGIN_FILE ),
				array( 'wp-element', 'wp-api-fetch' ),
				filemtime( plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'assets/js/inbox-app.js' ),
				true
			);
			$messages = array();
			if ( is_user_logged_in() ) {
				$messages = DirectMessages::list_conversations( get_current_user_id() );
			}
			wp_localize_script(
				'ap-inbox-app',
				'APInbox',
				array(
					'apiRoot'     => esc_url_raw( rest_url() ),
					'nonce'       => wp_create_nonce( 'wp_rest' ),
					'messages'    => $messages,
					'threadId'    => 0,
					'attachments' => array(),
				)
			);
		}
	}

	public static function render(): string {
		if ( ! is_user_logged_in() ) {
			return '<p>' . esc_html__( 'Please log in to view your inbox.', 'artpulse' ) . '</p>';
		}
		return '<div id="ap-inbox-app"></div>';
	}
}
