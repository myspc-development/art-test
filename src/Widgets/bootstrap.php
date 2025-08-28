<?php
namespace ArtPulse\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ArtPulse\Widgets\Organization\LeadCaptureWidget;
use ArtPulse\Widgets\Organization\RsvpStatsWidget;
use ArtPulse\Widgets\Organization\WebhooksWidget;
use ArtPulse\Widgets\Organization\MyEventsWidget;

add_action(
	'plugins_loaded',
	static function (): void {
		LeadCaptureWidget::boot();
		RsvpStatsWidget::boot();
		WebhooksWidget::boot();
		MyEventsWidget::boot();

		$common_dir = __DIR__ . '/Common';
		foreach ( glob( $common_dir . '/*Widget.php' ) as $file ) {
			$class = __NAMESPACE__ . '\\Common\\' . basename( $file, '.php' );
			if ( ! class_exists( $class ) ) {
				require_once $file;
			}
			if ( method_exists( $class, 'boot' ) ) {
				$class::boot();
			} elseif ( method_exists( $class, 'register' ) ) {
				$class::register();
			}
		}
	}
);
