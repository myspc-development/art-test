<?php
use PHPUnit\Framework\TestCase;

/**
 * @group l10n
 */
class TextDomainLoadTest extends TestCase {
    public function test_my_events_translation(): void {
        $pofile = dirname( dirname( __DIR__ ) ) . '/languages/artpulse-es_ES.po';
        $loaded = load_textdomain( 'artpulse', $pofile );
        $this->assertTrue( $loaded, 'Failed to load text domain.' );

        $translation = translate( 'My Events', 'artpulse' );
        $this->assertSame( 'Mis eventos', $translation, 'Missing translation for My Events.' );
    }
}

function load_textdomain( string $domain, string $pofile ): bool {
    global $l10n;
    if ( ! is_array( $l10n ) ) {
        $l10n = array();
    }
    $translations = parse_po_file( $pofile );
    if ( false === $translations ) {
        return false;
    }
    $l10n[ $domain ] = $translations;
    return true;
}

function translate( string $text, string $domain ) {
    global $l10n;
    return $l10n[ $domain ][ $text ] ?? $text;
}

function parse_po_file( string $pofile ) {
    if ( ! is_readable( $pofile ) ) {
        return false;
    }

    $translations = array();
    $lines        = file( $pofile );
    $msgid        = null;

    foreach ( $lines as $line ) {
        $line = trim( $line );

        if ( 0 === strpos( $line, 'msgid "' ) ) {
            $id = substr( $line, 7, -1 );
            $msgid = '' === $id ? null : stripcslashes( $id );
            continue;
        }

        if ( null !== $msgid && 0 === strpos( $line, 'msgstr "' ) ) {
            $str = substr( $line, 8, -1 );
            $translations[ $msgid ] = stripcslashes( $str );
            $msgid = null;
        }
    }

    return $translations;
}
