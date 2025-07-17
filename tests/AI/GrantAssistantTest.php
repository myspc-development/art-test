<?php
namespace ArtPulse\AI;

function sanitize_key($key) { return $key; }
function sanitize_textarea_field($text) { return $text; }
function esc_html($text) { return $text; }
function wpautop($text) { return '<p>'.$text.'</p>'; }
function rest_ensure_response($data) { return $data; }
function is_user_logged_in() { return true; }
function add_action($h, $cb) {}

use PHPUnit\Framework\TestCase;

class DummyRequest
{
    private array $p;
    public function __construct(array $p) { $this->p = $p; }
    public function get_param($k) { return $this->p[$k] ?? null; }
}

class GrantAssistantTest extends TestCase
{
    public function test_generate_returns_prompted_text(): void
    {
        $req = new DummyRequest([
            'type' => 'project_summary',
            'tone' => 'grant',
            'source' => 'Community arts event.',
        ]);
        $res = GrantAssistant::generate($req);
        $this->assertStringContainsString('Community arts event.', $res['draft']);
        $this->assertStringContainsString('<p>', $res['output']);
    }
}
