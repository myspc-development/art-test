<?php
namespace ArtPulse\Widgets\Artist;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ArtPulse\Core\DashboardWidgetInterface;
use ArtPulse\Core\DashboardWidgetRegistry;

class ArtistRevenueSummaryWidget implements DashboardWidgetInterface {

	public static function id(): string {
		return 'artist_revenue_summary';
	}

	public static function label(): string {
		return esc_html__( 'Revenue Summary', 'artpulse' );
	}

	public static function roles(): array {
		return array( 'artist' );
	}

	public static function description(): string {
		return esc_html__( 'Sales totals for your recent period.', 'artpulse' );
	}

	public static function register(): void {
		DashboardWidgetRegistry::register(
			self::id(),
			self::label(),
			'chart-line',
			self::description(),
			array( self::class, 'render' ),
			array(
				'roles'    => self::roles(),
				'category' => 'commerce',
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
			<div class="ap-widget__body">
				<p><?php echo esc_html__( 'Connect your store to view revenue.', 'artpulse' ); ?></p>
				<?php if ( function_exists( 'wp_script_is' ) && wp_script_is( 'chart.js', 'enqueued' ) ) : ?>
					<canvas class="ap-sparkline" height="40"></canvas>
				<?php else : ?>
					<p><?php echo esc_html__( 'Chart unavailable', 'artpulse' ); ?></p>
				<?php endif; ?>
			</div>
		</section>
		<?php
		return ob_get_clean();
	}
}

ArtistRevenueSummaryWidget::register();
