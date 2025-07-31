<?php
namespace ArtPulse\Core;

function esc_html($text) { return $text; }
function trailingslashit($path) { return rtrim($path, '/').'/'; }

namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DocumentGenerator;
use ArtPulse\Admin\Tests\Stub;

class DocumentGeneratorTest extends TestCase
{
    protected function setUp(): void
    {
        Stub::reset();
    }

    public function test_generate_ticket_pdf_returns_empty_when_dompdf_missing(): void
    {
        class_exists(DocumentGenerator::class);
        $loaders = spl_autoload_functions() ?: [];
        foreach ($loaders as $loader) {
            spl_autoload_unregister($loader);
        }

        $path = DocumentGenerator::generate_ticket_pdf(['event_title' => 'Test']);

        foreach ($loaders as $loader) {
            spl_autoload_register($loader);
        }

        $this->assertSame('', $path);
    }

    public function test_generate_ticket_pdf_returns_file_path(): void
    {
        Stub::$upload_path = sys_get_temp_dir();
        Stub::$password    = 'code';

        if (!class_exists('Dompdf\\Dompdf')) {
            eval('namespace Dompdf; class Dompdf { public function loadHtml($h){} public function setPaper($p){} public function render(){} public function output(){ return "PDF"; } }');
        }

        $path = DocumentGenerator::generate_ticket_pdf([
            'event_title' => 'Event',
            'ticket_code' => 'ABC123',
        ]);

        $this->assertNotEmpty($path);
        $this->assertStringContainsString('ticket-ABC123.pdf', $path);
        $this->assertFileExists($path);
        unlink($path);
    }
}
