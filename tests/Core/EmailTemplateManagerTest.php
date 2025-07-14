<?php
namespace ArtPulse\Core\Tests;

use ArtPulse\Core\EmailTemplateManager;
use WP_UnitTestCase;

class EmailTemplateManagerTest extends WP_UnitTestCase
{
    public function test_render_replaces_placeholders(): void
    {
        update_option('artpulse_settings', ['default_email_template' => '<div>Hello {{username}}, {{content}}</div>']);
        $output = EmailTemplateManager::render('Message body', ['username' => 'Bob']);
        $this->assertStringContainsString('Hello Bob, Message body', $output);
    }
}
