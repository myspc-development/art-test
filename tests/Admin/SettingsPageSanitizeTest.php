<?php
namespace ArtPulse\Admin\Tests;

use ArtPulse\Admin\SettingsPage;
use WP_UnitTestCase;

class SettingsPageSanitizeTest extends WP_UnitTestCase
{
    public function test_checkbox_unchecked_saves_zero(): void
    {
        update_option('artpulse_settings', ['debug_logging' => 1]);
        $input = ['debug_logging' => '0'];
        $result = SettingsPage::sanitizeSettings($input);
        $this->assertSame(0, $result['debug_logging']);
    }

    public function test_checkbox_checked_saves_one(): void
    {
        update_option('artpulse_settings', ['debug_logging' => 0]);
        $input = ['debug_logging' => '1'];
        $result = SettingsPage::sanitizeSettings($input);
        $this->assertSame(1, $result['debug_logging']);
    }
}
