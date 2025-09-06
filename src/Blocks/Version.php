<?php
namespace ArtPulse\Blocks;

function ap_block_version(): string {
	$ns = __NAMESPACE__ . '\ART_PULSE_VERSION';
	if ( defined( $ns ) ) {
		return constant( $ns );
	}
	if ( defined( 'ART_PULSE_VERSION' ) ) {
		return \ART_PULSE_VERSION;
	}
	return '0.1.0-test';
}
