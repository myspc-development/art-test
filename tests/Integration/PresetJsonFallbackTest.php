<?php

use ArtPulse\Core\DashboardPresets;
use ArtPulse\Dashboard\WidgetGuard;

class PresetJsonFallbackTest extends WP_UnitTestCase {
    private string $presetPath;
    private ?string $originalPreset = null;
    private array $errors = [];
    private $errHandler;

    public function set_up(): void {
        parent::set_up();
        $this->presetPath = dirname(__DIR__, 1) . '/../data/preset-member.json';
        if (is_readable($this->presetPath)) {
            $this->originalPreset = file_get_contents($this->presetPath);
        }
        DashboardPresets::resetCache();
        if (!get_role('member')) {
            add_role('member', 'Member');
        }
        wp_set_current_user(self::factory()->user->create(['role' => 'member']));
        $this->errors = [];
        $this->errHandler = set_error_handler(function ($errno, $errstr) {
            $this->errors[] = $errstr;
        });
    }

    public function tear_down(): void {
        restore_error_handler();
        DashboardPresets::resetCache();
        if ($this->originalPreset !== null) {
            file_put_contents($this->presetPath, $this->originalPreset);
        } elseif (file_exists($this->presetPath)) {
            unlink($this->presetPath);
        }
        parent::tear_down();
    }

    private function renderDashboard(): string {
        $slugs = DashboardPresets::forRole('member');
        foreach ($slugs as $slug) {
            WidgetGuard::register_stub_widget($slug, ['title' => $slug]);
        }
        return do_shortcode('[user_dashboard]');
    }

    public function test_missing_json_uses_fallback_and_no_warnings(): void {
        if (file_exists($this->presetPath)) {
            unlink($this->presetPath);
        }
        DashboardPresets::resetCache();
        $html = $this->renderDashboard();
        $this->assertStringContainsString('ap-widget--placeholder', $html);
        $this->assertSame([], $this->errors, 'No PHP warnings expected');
    }

    public function test_malformed_json_uses_fallback_and_no_warnings(): void {
        file_put_contents($this->presetPath, '{bad');
        DashboardPresets::resetCache();
        $html = $this->renderDashboard();
        $this->assertStringContainsString('ap-widget--placeholder', $html);
        $this->assertSame([], $this->errors, 'No PHP warnings expected');
    }
}

