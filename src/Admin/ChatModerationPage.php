<?php
namespace ArtPulse\Admin;

use function ArtPulse\DB\Chat\maybe_install_tables;

class ChatModerationPage {

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'add_menu' ) );
	}

	public static function add_menu(): void {
		add_submenu_page(
			'artpulse-settings',
			__( 'Chat Moderation', 'artpulse' ),
			__( 'Chat Moderation', 'artpulse' ),
			'moderate_comments',
			'ap-chat-moderation',
			array( self::class, 'render' )
		);
	}

	public static function render(): void {
		global $wpdb;
		maybe_install_tables();
		$table    = $wpdb->prefix . 'ap_event_chat';
		$messages = $wpdb->get_results( "SELECT * FROM $table ORDER BY created_at DESC LIMIT 100" );
		$nonce    = wp_create_nonce( 'wp_rest' );
		$root     = esc_url_raw( rest_url() );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Chat Moderation', 'artpulse' ); ?></h1>
			<table class="widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Time', 'artpulse' ); ?></th>
						<th><?php esc_html_e( 'User', 'artpulse' ); ?></th>
						<th><?php esc_html_e( 'Message', 'artpulse' ); ?></th>
						<th><?php esc_html_e( 'Flagged', 'artpulse' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'artpulse' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php
				foreach ( $messages as $m ) :
					$user = get_user_by( 'ID', $m->user_id );
					?>
					<tr data-id="<?php echo esc_attr( $m->id ); ?>">
						<td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $m->created_at ) ) ); ?></td>
						<td><?php echo esc_html( $user ? $user->display_name : $m->user_id ); ?></td>
						<td><?php echo esc_html( wp_trim_words( $m->content, 20 ) ); ?></td>
						<td class="flagged"><?php echo $m->flagged ? __( 'Yes', 'artpulse' ) : '—'; ?></td>
						<td>
							<button class="button ap-chat-flag" data-id="<?php echo esc_attr( $m->id ); ?>"><?php esc_html_e( 'Flag as Inappropriate', 'artpulse' ); ?></button>
							<button class="button ap-chat-delete" data-id="<?php echo esc_attr( $m->id ); ?>"><?php esc_html_e( 'Delete', 'artpulse' ); ?></button>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<script>
		const apiRoot = <?php echo wp_json_encode( $root ); ?>;
		const nonce = <?php echo wp_json_encode( $nonce ); ?>;
		document.querySelectorAll('.ap-chat-delete').forEach(btn=>{
			btn.addEventListener('click',()=>{
				if(!confirm('Delete this message?')) return;
				fetch(`${apiRoot}artpulse/v1/chat/${btn.dataset.id}`, {method:'DELETE', headers:{'X-WP-Nonce':nonce}})
					.then(()=>btn.closest('tr').remove());
			});
		});
		document.querySelectorAll('.ap-chat-flag').forEach(btn=>{
			btn.addEventListener('click',()=>{
				fetch(`${apiRoot}artpulse/v1/chat/${btn.dataset.id}/flag`, {method:'POST', headers:{'X-WP-Nonce':nonce}})
					.then(()=>btn.closest('tr').querySelector('.flagged').textContent='Yes');
			});
		});
		</script>
		<?php
	}
}
