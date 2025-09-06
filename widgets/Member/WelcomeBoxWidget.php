<?php
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}
namespace ArtPulse\Widgets\Member;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use ArtPulse\Core\DashboardWidgetInterface;
use ArtPulse\Core\DashboardWidgetRegistry;

class WelcomeBoxWidget implements DashboardWidgetInterface {
	public static function id(): string {
		return 'welcome_box'; }

	public static function label(): string {
		return esc_html__( 'Welcome', 'artpulse' ); }

	public static function roles(): array {
		return array( 'member' ); }

	public static function description(): string {
		return esc_html__( 'Personal greeting for the signed-in user.', 'artpulse' ); }

	public static function register(): void {
		DashboardWidgetRegistry::register(
			self::id(),
			self::label(),
			'smiley',
			self::description(),
			array( self::class, 'render' ),
			array(
				'roles'    => self::roles(),
				'category' => 'general',
			)
		);
	}

	public static function render( int $user_id = 0 ): string {
		$user               = wp_get_current_user();
		$name               = $user->display_name ?: $user->user_login;
				$text       = sprintf( esc_html__( 'Welcome back, %1$s!', 'artpulse' ), esc_html( $name ) );
				$heading_id = sanitize_title( self::id() ) . '-heading-' . uniqid();

		ob_start();
		?>
		<section role="region" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>"
			data-widget-id="<?php echo esc_attr( self::id() ); ?>"
			class="ap-widget ap-<?php echo esc_attr( self::id() ); ?>">
			<h2 id="<?php echo esc_attr( $heading_id ); ?>"><?php echo self::label(); ?></h2>
						<p><?php echo $text; ?></p>
		</section>
		<?php
		return ob_get_clean();
	}
}

WelcomeBoxWidget::register();
