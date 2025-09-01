<?php
namespace ArtPulse\Core {
	if ( ! class_exists( \ArtPulse\Core\DashboardWidgetRegistry::class ) ) {
		/**
		 * @group PHPUNIT
		 */
		class DashboardWidgetRegistry {
			public static function register( ...$args ): void {}
		}
	}
}

namespace ArtPulse\Widgets\Member {
	function wp_get_current_user() {
		return \ArtPulse\Tests\WelcomeBoxWidgetTest::$user;
	}
}

namespace ArtPulse\Tests {
	require_once __DIR__ . '/../TestStubs.php';

	use ArtPulse\Widgets\Member\WelcomeBoxWidget;
	use PHPUnit\Framework\TestCase;

	class WelcomeBoxWidgetTest extends TestCase {
		public static $user;

		protected function setUp(): void {
			self::$user = (object) array(
				'display_name' => 'Tester',
				'user_login'   => 'tester',
			);
		}

		public function test_render_includes_display_name(): void {
			$html = WelcomeBoxWidget::render();
			$this->assertStringContainsString( 'Welcome back, Tester!', $html );
		}
	}
}
