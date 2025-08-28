<?php
use ArtPulse\AI\AutoTagger;
use ArtPulse\AI\BioSummaryRestController;
use ArtPulse\AI\AutoTaggerRestController;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

AutoTagger::register();

// Register custom REST API controllers when the REST API is initialized.
add_action(
	'rest_api_init',
	static function () {
		BioSummaryRestController::register_routes();
		AutoTaggerRestController::register_routes();
	}
);
