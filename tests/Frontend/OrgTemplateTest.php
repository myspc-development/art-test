<?php
namespace {
    function get_header(){}
    function get_footer(){}
    function have_posts(){ return \ArtPulse\Frontend\Tests\OrgTemplateTest::$have_posts; }
    function the_post(){ \ArtPulse\Frontend\Tests\OrgTemplateTest::$have_posts = false; }
    function the_post_thumbnail($size,$attr=[]){}
    function the_title(){ echo 'Test Org'; }
    function the_content(){ echo 'Org Content'; }
    function get_the_ID(){ return 1; }
    function get_post_meta($id,$key,$single=false){ return \ArtPulse\Frontend\Tests\OrgTemplateTest::$meta[$key] ?? ''; }
    function esc_html_e($text,$domain=null){ echo $text; }
    function esc_html($text){ return $text; }
    function esc_url($url){ return $url; }
}

namespace ArtPulse\Frontend\Tests;

use PHPUnit\Framework\TestCase;

class OrgTemplateTest extends TestCase
{
    public static bool $have_posts = true;
    public static array $meta = [];

    protected function setUp(): void
    {
        self::$have_posts = true;
        self::$meta = [];
    }

    public function test_opening_hours_displayed(): void
    {
        self::$meta = [
            'ead_org_street_address' => '123',
            'ead_org_monday_start_time' => '09:00',
            'ead_org_monday_end_time' => '17:00',
        ];
        ob_start();
        include __DIR__ . '/../../templates/salient/content-artpulse_org.php';
        $html = ob_get_clean();
        $this->assertStringContainsString('Opening Hours', $html);
        $this->assertStringContainsString('Monday', $html);
        $this->assertStringContainsString('09:00 - 17:00', $html);
    }
}
