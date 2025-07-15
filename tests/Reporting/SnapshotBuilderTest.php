<?php
namespace ArtPulse\Reporting;

function esc_html($text) { return $text; }
function trailingslashit($path) { return rtrim($path, '/').'/'; }
function wp_upload_dir() { return ['path' => \ArtPulse\Admin\Tests\Stub::$upload_path]; }
function wp_generate_password($len = 12, $special = false) { return \ArtPulse\Admin\Tests\Stub::$password; }

namespace ArtPulse\Reporting\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Reporting\SnapshotBuilder;
use ArtPulse\Admin\Tests\Stub;

class SnapshotBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        Stub::reset();
    }

    public function test_generate_pdf_returns_file_path(): void
    {
        Stub::$upload_path = sys_get_temp_dir();
        Stub::$password    = 'snap';

        if (!class_exists('Dompdf\\Dompdf')) {
            eval('namespace Dompdf; class Dompdf { public function loadHtml($h){} public function setPaper($p){} public function render(){} public function output(){ return "PDF"; } }');
        }

        $path = SnapshotBuilder::generate_pdf([
            'title' => 'Monthly Report',
            'data'  => ['RSVPs' => 25],
        ]);

        $this->assertNotEmpty($path);
        $this->assertFileExists($path);
        unlink($path);
    }

    public function test_generate_csv_returns_file_path(): void
    {
        Stub::$upload_path = sys_get_temp_dir();
        Stub::$password    = 'snap';

        $path = SnapshotBuilder::generate_csv([
            'title' => 'Monthly Report',
            'data'  => ['RSVPs' => 25],
        ]);

        $this->assertNotEmpty($path);
        $this->assertFileExists($path);
        unlink($path);
    }
}
