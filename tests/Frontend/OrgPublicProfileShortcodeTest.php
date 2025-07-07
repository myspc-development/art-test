<?php
namespace ArtPulse\Frontend;

function get_post_meta($id,$key,$single=false){return OrgPublicProfileShortcodeTest::$meta[$key] ?? '';}
function get_page_by_path($path,$output,$type){return OrgPublicProfileShortcodeTest::$page;}
function get_post($id){return (object)['ID'=>$id,'post_title'=>'My Org'];}
function wp_get_attachment_url($id){return 'img'.$id.'.jpg';}
function esc_html($t){return $t;}
function esc_html_e($t,$d=null){}
function esc_url($u){return $u;}
function get_permalink($id){return '/event/'.$id;}
function get_the_title($id){return 'Event '.$id;}
function wpautop($t){return $t;}
function sanitize_title($s){return $s;}
function shortcode_atts($pairs,$atts,$tag){return array_merge($pairs,$atts);} 
function absint($n){return (int)$n;}
function esc_attr($t){return $t;}

namespace ArtPulse\Frontend\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Frontend\OrgPublicProfileShortcode;

class OrgPublicProfileShortcodeTest extends TestCase
{
    public static array $meta = [];
    public static $page = null;

    protected function setUp(): void
    {
        self::$meta = [
            'ap_org_profile_published' => '1',
            'ap_org_tagline' => 'Best Org',
            'ap_org_theme_color' => '#abc',
            'ead_org_logo_id' => 4,
            'ead_org_banner_id' => 5,
            'ead_org_description' => 'About us',
            'ap_org_featured_events' => '2,3',
        ];
        self::$page = null;
    }

    public function test_render_outputs_tagline(): void
    {
        $html = OrgPublicProfileShortcode::render(['id' => 1]);
        $this->assertStringContainsString('Best Org', $html);
        $this->assertStringContainsString('img4.jpg', $html);
        $this->assertStringContainsString('Event 2', $html);
    }
}
