<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Widgets\OrgAnalyticsWidget;
use ArtPulse\Widgets\EventsWidget;
use ArtPulse\Widgets\DonationsWidget;

class WidgetVisibilitySettingsTest extends \WP_UnitTestCase {
    public function set_up(): void {
        parent::set_up();
        // reset registry
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue([]);
        delete_option('artpulse_widget_roles');

        EventsWidget::register();
        DonationsWidget::register();
        OrgAnalyticsWidget::register();
    }

    public function test_settings_override_roles_and_capability(): void {
        DashboardWidgetRegistry::register_widget('test_widget', [
            'label' => 'Test Widget',
            'callback' => '__return_null',
            'roles' => ['member'],
        ]);

        update_option('artpulse_widget_roles', [
            'test_widget' => [
                'roles' => ['organization'],
                'capability' => 'edit_posts',
            ],
        ]);

        $all = DashboardWidgetRegistry::get_all();
        $this->assertSame(['organization'], $all['test_widget']['roles']);
        $this->assertSame('edit_posts', $all['test_widget']['capability']);
    }

    public function test_org_analytics_widget_fallback_for_non_capable_user(): void {
        $uid = self::factory()->user->create(['role' => 'subscriber']);
        wp_set_current_user($uid);

        $html = OrgAnalyticsWidget::render($uid);
        $this->assertStringContainsString('notice-error', $html);
    }

    public function test_org_analytics_widget_renders_for_capable_user(): void {
        $uid = self::factory()->user->create(['role' => 'organization']);
        wp_set_current_user($uid);

        $html = OrgAnalyticsWidget::render($uid);
        $this->assertStringNotContainsString('notice-error', $html);
        $this->assertStringContainsString('Basic traffic', $html);
    }

    /**
     * @runInSeparateProcess
     */
    public function test_org_analytics_widget_hidden_in_builder_preview(): void {
        define('IS_DASHBOARD_BUILDER_PREVIEW', true);
        $uid = self::factory()->user->create(['role' => 'subscriber']);
        wp_set_current_user($uid);

        $html = OrgAnalyticsWidget::render($uid);
        $this->assertSame('', $html);
    }

    public function test_visibility_page_reflects_saved_settings(): void {
        DashboardWidgetRegistry::register_widget('test_widget', [
            'label' => 'Test Widget',
            'callback' => '__return_null',
            'roles' => ['member'],
        ]);

        update_option('artpulse_widget_roles', [
            'test_widget' => [
                'roles' => ['member'],
                'capability' => 'edit_posts',
            ],
        ]);

        ob_start();
        ap_render_widget_visibility_page();
        $html = ob_get_clean();

        $this->assertStringContainsString('value="member" checked', $html);
        $this->assertStringContainsString('value="edit_posts"', $html);
    }

    public function test_widget_visibility_per_role(): void {
        $member = self::factory()->user->create(['role' => 'member']);
        wp_set_current_user($member);
        ob_start();
        DashboardWidgetRegistry::render_for_role($member);
        $html = ob_get_clean();
        $this->assertStringContainsString('Events content.', $html);
        $this->assertStringNotContainsString('Example donations', $html);
        $this->assertStringContainsString('<h2>Insights</h2>', $html);
        $this->assertStringNotContainsString('<h2>Actions</h2>', $html);

        $sub = self::factory()->user->create(['role' => 'subscriber']);
        wp_set_current_user($sub);
        ob_start();
        DashboardWidgetRegistry::render_for_role($sub);
        $html = ob_get_clean();
        $this->assertStringNotContainsString('Events content.', $html);
        $this->assertStringNotContainsString('Example donations', $html);
        $this->assertStringNotContainsString('Basic traffic', $html);

        $org_role = get_role('organization');
        if ($org_role) {
            $org_role->add_cap('view_analytics');
        }
        $org = self::factory()->user->create(['role' => 'organization']);
        wp_set_current_user($org);
        ob_start();
        DashboardWidgetRegistry::render_for_role($org);
        $html = ob_get_clean();
        $this->assertStringContainsString('Example donations', $html);
        $this->assertStringContainsString('Basic traffic', $html);
        $this->assertStringContainsString('<h2>Insights</h2>', $html);
        $this->assertStringContainsString('<h2>Actions</h2>', $html);
    }

    public function test_donations_template_override_loaded(): void {
        $dir = sys_get_temp_dir() . '/ap-theme-' . wp_generate_password(8, false, false);
        mkdir($dir . '/templates/widgets', 0777, true);
        file_put_contents($dir . '/templates/widgets/donations.php', '<p>override</p>');
        $filter = static function() use ($dir) { return $dir; };
        add_filter('stylesheet_directory', $filter);

        $uid = self::factory()->user->create(['role' => 'organization']);
        wp_set_current_user($uid);

        $html = DonationsWidget::render($uid);

        remove_filter('stylesheet_directory', $filter);
        self::recursiveRemoveDir($dir);

        $this->assertStringContainsString('override', $html);
    }

    public function test_donations_template_falls_back_when_missing(): void {
        $uid = self::factory()->user->create(['role' => 'organization']);
        wp_set_current_user($uid);

        $html = DonationsWidget::render($uid);

        $this->assertStringContainsString('Example donations', $html);
    }

    private static function recursiveRemoveDir(string $dir): void {
        if (!is_dir($dir)) return;
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
        }
        rmdir($dir);
    }
}
