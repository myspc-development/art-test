<?php
namespace ArtPulse;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WidgetLogger {

	private string $file;

	private array $levels = array(
		'debug'   => 0,
		'info'    => 1,
		'warning' => 2,
		'error'   => 3,
	);

	public function __construct() {
		$uploads    = wp_upload_dir();
		$basedir    = $uploads['basedir'] ?? sys_get_temp_dir();
		$this->file = rtrim( $basedir, '/\\' ) . '/ap-widget.log';

		add_action( 'ap_widget_rendered', array( $this, 'logRendered' ), 10, 2 );
		add_action( 'ap_widget_hidden', array( $this, 'logHidden' ), 10, 2 );
	}

	public function logRendered( string $widget_id, int $user_id ): void {
                $this->log( 'info', sprintf( 'Widget %1$s rendered for user %2$d', $widget_id, $user_id ) );
	}

	public function logHidden( string $widget_id, int $user_id ): void {
                $this->log( 'info', sprintf( 'Widget %1$s hidden for user %2$d', $widget_id, $user_id ) );
	}

	private function log( string $level, string $message ): void {
		$threshold      = apply_filters( 'ap_widget_log_level', 'info' );
		$thresholdLevel = $this->levels[ strtolower( $threshold ) ] ?? $this->levels['info'];
		$currentLevel   = $this->levels[ strtolower( $level ) ] ?? $this->levels['info'];

		if ( $currentLevel < $thresholdLevel ) {
			return;
		}

		$timestamp = gmdate( 'c' );
                $line      = sprintf( '[%1$s] %2$s: %3$s', $timestamp, strtoupper( $level ), $message );
		file_put_contents( $this->file, $line . PHP_EOL, FILE_APPEND );
	}
}
