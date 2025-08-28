<?php
namespace ArtPulse\Widgets\Common;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ArtPulse\Core\DashboardWidgetInterface;
use ArtPulse\Core\DashboardWidgetRegistry;

class SiteStatsWidget implements DashboardWidgetInterface {

	public static function id(): string {
		return 'site_stats';
	}

	public static function label(): string {
		return esc_html__( 'Site Stats', 'artpulse' );
	}

	public static function roles(): array {
		return array( 'artist', 'member', 'organization', 'administrator' );
	}

	public static function description(): string {
		return esc_html__( 'Overall site traffic and engagement metrics.', 'artpulse' );
	}

	public static function boot(): void {
		add_action( 'artpulse/widgets/register', array( self::class, 'register' ), 10, 1 );
	}

	public static function register(): void {
		DashboardWidgetRegistry::register(
			self::id(),
			self::label(),
			'chart-bar',
			self::description(),
			array( self::class, 'render' ),
			array(
				'roles'    => self::roles(),
				'category' => 'analytics',
			)
		);
	}

	public static function render( int $user_id = 0 ): string {
		$heading_id = self::id() . '-heading';
		ob_start();
		?>
		<section role="region" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>"
			data-widget="<?php echo esc_attr( self::id() ); ?>"
			data-widget-id="<?php echo esc_attr( self::id() ); ?>"
			class="ap-widget ap-<?php echo esc_attr( self::id() ); ?>">
			<h2 id="<?php echo esc_attr( $heading_id ); ?>"><?php echo esc_html( self::label() ); ?></h2>
			<p><?php echo esc_html__( 'Traffic data is currently unavailable.', 'artpulse' ); ?></p>
		</section>
		<?php
		return ob_get_clean();
	}
}

