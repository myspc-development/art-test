<?php
namespace ArtPulse\AI\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\AI\AutoTagger;

/**

 * @group AI

 */

class AutoTaggerTest extends TestCase {

	/**
	 * @dataProvider languageSamples
	 */
	public function test_detect_language( string $text, string $expected ): void {
		$ref = new \ReflectionMethod( AutoTagger::class, 'detect_language' );
		$ref->setAccessible( true );
		$this->assertSame( $expected, $ref->invoke( null, $text ) );
	}

	public static function languageSamples(): array {
                return array(
                        // Spanish with diacritics.
                        array( 'Canción para niño.', 'es' ),
                        // Spanish using common stopwords without accents.
                        array( 'Esto es una prueba.', 'es' ),
                        // French with diacritics.
                        array( 'école française', 'fr' ),
                        // French using common stopwords without accents.
                        array( 'Ceci est un test', 'fr' ),
                        // German with diacritics.
                        array( 'Füße müde', 'de' ),
                        // German using common stopwords without accents.
                        array( 'Dies ist ein Test', 'de' ),
                        array( 'Это тест', 'ru' ),
                        array( '这是一个测试', 'zh' ),
                        array( 'A simple test', 'en' ),
                );
        }
}
