<?php
namespace ArtPulse\Util;

/**
 * Obfuscate an email address for safe display.
 *
 * Converts any literal or encoded at symbols to the HTML entity
 * "&#64;". This is a lightweight alternative to WordPress'
 * `antispambot()` and performs no additional escaping.
 *
 * @param string $email Email address.
 * @return string Obfuscated email address.
 */
function ap_obfuscate_email( $email ): string {
	return str_ireplace( array( '@', '&#064;', '&#x40;' ), '&#64;', $email );
}
