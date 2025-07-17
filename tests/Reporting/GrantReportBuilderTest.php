<?php
namespace ArtPulse\Reporting;

function esc_html($text) { return $text; }
function trailingslashit($path) { return rtrim($path, '/').'/'; }
function wp_upload_dir() { return ['path' => \ArtPulse\Admin\Tests\Stub::$upload_path]; }

namespace ArtPulse\Reporting\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Reporting\GrantReportBuilder;
use ArtPulse\Admin\Tests\Stub;

class GrantReportBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        Stub::reset();
    }

    public function test_generate_pdf_creates_file(): void
    {
        Stub::$upload_path = sys_get_temp_dir();

        if (!class_exists('Dompdf\\Dompdf')) {
            eval('namespace Dompdf; class Dompdf { public function loadHtml($h){} public function setPaper($p){} public function render(){} public function output(){ return "PDF"; } }');
        }

        $path = GrantReportBuilder::generate_pdf([
            'title'   => 'Grant Report',
            'summary' => ['Events' => 2],
            'events'  => [ ['title' => 'Show', 'rsvps' => 10] ],
            'donors'  => [ ['name' => 'Alice', 'amount' => '100'] ],
        ]);

        $this->assertNotEmpty($path);
        $this->assertFileExists($path);
        unlink($path);
    }
}
