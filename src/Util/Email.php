<?php
namespace ArtPulse\Util;

/**
 * Obfuscate an email address for safe display.
 *
 * Note: The returned string is not escaped. Callers must escape the
 * result before outputting it in HTML.
 *
 * @param string $email Email address.
 * @return string Obfuscated email address.
 */
function ap_obfuscate_email( $email ): string {
    $obfuscated = antispambot( $email );
    return str_replace( array( '@', '&#064;', '&#x40;' ), '&#64;', $obfuscated );
}
