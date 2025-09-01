<?php
namespace ArtPulse\Blocks;

/**
 * Resolve the plugin version for block assets.
 *
 * @return string
 */
function ap_block_version(): string {
	return defined( 'ART_PULSE_VERSION' ) ? ART_PULSE_VERSION : '0.1.0-test';
}
