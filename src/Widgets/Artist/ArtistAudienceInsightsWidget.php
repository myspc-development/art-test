<?php
namespace ArtPulse\Widgets\Artist;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ArtPulse\Core\DashboardWidgetInterface;
use ArtPulse\Core\DashboardWidgetRegistry;

class ArtistAudienceInsightsWidget implements DashboardWidgetInterface {

	public static function id(): string {
		return 'artist_audience_insights';
	}

	public static function label(): string {
		return esc_html__( 'Audience Insights', 'artpulse' );
	}

	public static function roles(): array {
		return array( 'artist' );
	}

	public static function description(): string {
		return esc_html__( 'Follower analytics and engagement.', 'artpulse' );
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
			<p><?php echo esc_html__( 'No audience data available.', 'artpulse' ); ?></p>
		</section>
		<?php
		return ob_get_clean();
	}
}

ArtistAudienceInsightsWidget::register();
