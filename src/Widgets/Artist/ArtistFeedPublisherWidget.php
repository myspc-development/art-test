<?php
namespace ArtPulse\Widgets\Artist;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ArtPulse\Core\DashboardWidgetInterface;
use ArtPulse\Core\DashboardWidgetRegistry;

class ArtistFeedPublisherWidget implements DashboardWidgetInterface {

	public static function id(): string {
		return 'artist_feed_publisher';
	}

	public static function label(): string {
		return esc_html__( 'Post & Engage', 'artpulse' );
	}

	public static function roles(): array {
		return array( 'artist' );
	}

	public static function description(): string {
		return esc_html__( 'Publish updates to your feed.', 'artpulse' );
	}

	public static function register(): void {
		DashboardWidgetRegistry::register(
			self::id(),
			self::label(),
			'edit',
			self::description(),
			array( self::class, 'render' ),
			array(
				'roles'    => self::roles(),
				'category' => 'community',
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
			<form method="post" class="ap-feed-composer">
				<textarea name="ap_status" rows="3" placeholder="<?php echo esc_attr( esc_html__( 'Share an update...', 'artpulse' ) ); ?>"></textarea>
				<p><button type="submit"><?php echo esc_html__( 'Post', 'artpulse' ); ?></button></p>
			</form>
		</section>
		<?php
		return ob_get_clean();
	}
}

ArtistFeedPublisherWidget::register();
