<?php
namespace ArtPulse\Util;

/**
 * Obfuscate email for safe display.
 *
 * @param string $email Email address.
 * @return string Escaped, obfuscated email.
 */
function ap_obfuscate_email( $email ): string {
    $obfuscated = antispambot( $email );
    $obfuscated = str_replace( array( '@', '&#064;', '&#x40;' ), '&#64;', $obfuscated );
    return esc_html( $obfuscated );
}
