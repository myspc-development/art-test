<?php
namespace ArtPulse\Frontend;

// WordPress function stubs
function is_user_logged_in() { return true; }
function wp_verify_nonce($nonce, $action) { return true; }
function get_current_user_id() { return 1; }
function sanitize_text_field($value) { return is_string($value) ? trim($value) : $value; }
function wp_kses_post($value) { return $value; }
function sanitize_email($value) { return $value; }
function get_posts($args) { return EventSubmissionShortcodeTest::$posts_return; }
function get_user_meta($uid, $key, $single = false) { return EventSubmissionShortcodeTest::$user_meta[$uid][$key] ?? ''; }
function wp_list_pluck($input, $field) { return array_map(fn($i) => is_object($i) ? $i->$field : $i[$field], $input); }
function wc_add_notice($msg, $type = '') { EventSubmissionShortcodeTest::$notice = $msg; }
function wp_die($msg) { EventSubmissionShortcodeTest::$notice = $msg; }

// Minimal stubs for unused functions to avoid errors if called
function wp_insert_post($arr) { EventSubmissionShortcodeTest::$inserted = $arr; return 1; }
function update_post_meta(...$args) { EventSubmissionShortcodeTest::$meta_updates[] = $args; }
function media_handle_upload($file, $post_id) { return EventSubmissionShortcodeTest::$media_ids[$file] ?? 0; }
function set_post_thumbnail($post_id, $thumb_id) { EventSubmissionShortcodeTest::$thumbnail = $thumb_id; }
function is_wp_error($obj) { return false; }
function wp_set_post_terms(...$args) {}
function function_exists($name) { return $name === 'wc_add_notice'; }

namespace ArtPulse\Frontend\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Frontend\EventSubmissionShortcode;

class EventSubmissionShortcodeTest extends TestCase
{
    public static array $posts_return = [];
    public static array $user_meta = [];
    public static string $notice = '';
    public static array $inserted = [];
    public static array $meta_updates = [];
    public static array $media_ids = [];
    public static int $thumbnail = 0;

    protected function setUp(): void
    {
        self::$posts_return = [];
        self::$user_meta = [];
        self::$notice = '';
        self::$inserted = [];
        self::$meta_updates = [];
        self::$media_ids = [];
        self::$thumbnail = 0;

        // Required POST fields
        $_POST = [
            'ap_submit_event' => 1,
            'ap_event_nonce'  => 'nonce',
            'event_title'     => 'title',
            'event_description'=> 'desc',
            'event_date'      => '2024-01-01',
            'event_org'       => 99,
        ];
    }

    public function test_invalid_org_rejected(): void
    {
        // Authorized org id is 5, selected org 99 should fail
        self::$user_meta[1]['ap_organization_id'] = 5;
        self::$posts_return = [];

        EventSubmissionShortcode::maybe_handle_form();

        $this->assertSame('Invalid organization selected.', self::$notice);
        $this->assertEmpty(self::$inserted);
    }

    public function test_start_date_after_end_date_rejected(): void
    {
        // Valid organization to avoid org failure
        self::$user_meta[1]['ap_organization_id'] = 99;

        $_POST['event_start_date'] = '2024-02-01';
        $_POST['event_end_date']   = '2024-01-01';

        EventSubmissionShortcode::maybe_handle_form();

        $this->assertSame('Start date cannot be later than end date.', self::$notice);
        $this->assertEmpty(self::$inserted);
    }

    public function test_banner_included_in_submission_images(): void
    {
        // Valid organization
        self::$user_meta[1]['ap_organization_id'] = 99;

        // Pretend banner uploaded and media handler returns ID 55
        self::$media_ids = ['event_banner' => 55];
        $_FILES['event_banner'] = ['name' => 'b.jpg', 'tmp_name' => '/tmp/b', 'type' => 'image/jpeg', 'error' => 0, 'size' => 1];

        EventSubmissionShortcode::maybe_handle_form();

        $gallery = null;
        foreach (self::$meta_updates as $args) {
            if ($args[1] === '_ap_submission_images') {
                $gallery = $args[2];
            }
        }

        $this->assertSame([55], $gallery);
    }
}
