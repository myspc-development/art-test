<?php
namespace ArtPulse\Widgets\Organization;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ArtPulse\Core\DashboardWidgetInterface;
use ArtPulse\Core\DashboardWidgetRegistry;

class LeadCaptureWidget implements DashboardWidgetInterface {

	public static function id(): string {
		return 'widget_audience_crm';
	}

	public static function label(): string {
		return esc_html__( 'Lead Capture', 'artpulse' );
	}

	public static function roles(): array {
		return array( 'organization', 'administrator' );
	}

	public static function description(): string {
		return esc_html__( 'Latest submissions and leads.', 'artpulse' );
	}

	public static function boot(): void {
		add_action( 'artpulse/widgets/register', array( self::class, 'register' ), 10, 1 );
	}

	public static function register( $registry = null ): void {
		DashboardWidgetRegistry::register(
			self::id(),
			self::label(),
			'email',
			self::description(),
			array( self::class, 'render' ),
			array(
				'roles'      => self::roles(),
				'capability' => 'ap_manage_org',
				'category'   => 'engagement',
			)
		);
	}

	public static function render( int $user_id = 0 ): string {
		if ( ! current_user_can( 'ap_manage_org' ) && ! current_user_can( 'manage_options' ) ) {
			$heading_id = self::id() . '-heading';
			ob_start();
			?>
			<section role="region" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>"
				data-widget="<?php echo esc_attr( self::id() ); ?>"
				data-widget-id="<?php echo esc_attr( self::id() ); ?>"
				class="ap-widget ap-<?php echo esc_attr( self::id() ); ?>">
				<h2 id="<?php echo esc_attr( $heading_id ); ?>"><?php echo esc_html( self::label() ); ?></h2>
				<p><?php echo esc_html__( 'Permission denied', 'artpulse' ); ?></p>
			</section>
			<?php
			return ob_get_clean();
		}
               $heading_id   = self::id() . '-heading';
               $download_url = ( function_exists( 'admin_url' ) && function_exists( 'wp_nonce_url' ) ) ? wp_nonce_url( admin_url( 'admin-post.php?action=ap_export_leads' ), 'ap_export_leads' ) : '#';
               $crm_url      = function_exists( 'admin_url' ) ? admin_url( 'admin.php?page=ap-crm-sync' ) : '#';
		ob_start();
		?>
		<section role="region" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>"
			data-widget="<?php echo esc_attr( self::id() ); ?>"
			data-widget-id="<?php echo esc_attr( self::id() ); ?>"
			class="ap-widget ap-<?php echo esc_attr( self::id() ); ?>">
			<h2 id="<?php echo esc_attr( $heading_id ); ?>"><?php echo esc_html( self::label() ); ?></h2>
			<p><?php echo esc_html__( 'No leads found.', 'artpulse' ); ?></p>
			<p>
				<a class="ap-widget__cta" href="<?php echo esc_url( $download_url ); ?>"><?php echo esc_html__( 'Download CSV', 'artpulse' ); ?></a>
				<a class="ap-widget__cta" href="<?php echo esc_url( $crm_url ); ?>"><?php echo esc_html__( 'Open CRM Sync', 'artpulse' ); ?></a>
			</p>
		</section>
		<?php
		return ob_get_clean();
	}
}

