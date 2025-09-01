<?php
namespace ArtPulse\AI\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\AI\AutoTagger;

/**

 * @group ai

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
			array( 'Esto es una prueba.', 'es' ),
			array( 'Это тест', 'ru' ),
			array( 'Ceci est un test', 'fr' ),
			array( 'Dies ist ein Test', 'de' ),
			array( '这是一个测试', 'zh' ),
			array( 'A simple test', 'en' ),
		);
	}
}
