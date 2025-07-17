<?php
namespace ArtPulse\AI\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\AI\AutoTagger;

class AutoTaggerTest extends TestCase
{
    /**
     * @dataProvider languageSamples
     */
    public function test_detect_language(string $text, string $expected): void
    {
        $ref = new \ReflectionMethod(AutoTagger::class, 'detect_language');
        $ref->setAccessible(true);
        $this->assertSame($expected, $ref->invoke(null, $text));
    }

    public static function languageSamples(): array
    {
        return [
            ['Esto es una prueba.', 'es'],
            ['Это тест', 'ru'],
            ['Ceci est un test', 'fr'],
            ['Dies ist ein Test', 'de'],
            ['这是一个测试', 'zh'],
            ['A simple test', 'en'],
        ];
    }
}
