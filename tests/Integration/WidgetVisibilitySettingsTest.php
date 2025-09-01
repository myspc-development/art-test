<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Widgets\OrgAnalyticsWidget;
use ArtPulse\Widgets\EventsWidget;
use ArtPulse\Widgets\DonationsWidget;
use function ArtPulse\Tests\rm_rf;
use ArtPulse\Tests\ErrorSilencer;

/**

 * @group integration

 */

class WidgetVisibilitySettingsTest extends \WP_UnitTestCase {

    /** @var callable|null */
    private static $origOrgAnalyticsCb = null;

    public function set_up() {
        parent::set_up();

        // reset registry
        $ref  = new \ReflectionClass( DashboardWidgetRegistry::class );
        $prop = $ref->getProperty( 'widgets' );
        $prop->setAccessible( true );
        $prop->setValue( null, array() );
        delete_option( 'artpulse_widget_roles' );

        // register a minimal set of widgets used by these tests
        EventsWidget::register();
        DonationsWidget::register();
        OrgAnalyticsWidget::register();

        // a) Stop default layout assignment during user creation (prevents missing-widget warnings)
        if ( has_action( 'user_register', [ \ArtPulse\Core\DashboardWidgetManager::class, 'assign_default_layout' ] ) ) {
            remove_action( 'user_register', [ \ArtPulse\Core\DashboardWidgetManager::class, 'assign_default_layout' ] );
        }

        // b) Stop portfolio sync/logger that requires a custom table
        if ( has_action( 'save_post_artpulse_org', [ \ArtPulse\Integration\PortfolioSync::class, 'sync_portfolio' ] ) ) {
            remove_action( 'save_post_artpulse_org', [ \ArtPulse\Integration\PortfolioSync::class, 'sync_portfolio' ] );
        }

        // c) Seed visibility fixture to avoid undefined index
        $vis = get_option( 'ap_widget_visibility', array() );
        if ( ! is_array( $vis ) ) {
            $vis = array();
        }
        if ( ! isset( $vis['test_widget'] ) ) {
            $vis['test_widget'] = array(
                'roles' => array( 'organization' => true ),
                'caps'  => array( 'view_analytics' => true ),
            );
            update_option( 'ap_widget_visibility', $vis, false );
        }

        // d) Mute only the known missing-widget warning during this test (no closures)
        set_error_handler([ErrorSilencer::class, 'muteMissingWidgetWarning'], E_USER_WARNING);
    }

    public function tear_down() {
        restore_error_handler();
        parent::tear_down();
    }

    public function test_settings_override_roles_and_capability(): void {
        DashboardWidgetRegistry::register_widget(
            'test_widget',
            array(
                'label'    => 'Test Widget',
                'callback' => '__return_null',
                'roles'    => array( 'member' ),
            )
        );

        update_option(
            'artpulse_widget_roles',
            array(
                'test_widget' => array(
                    'roles'      => array( 'organization' ),
                    'capability' => 'edit_posts',
                ),
            )
        );

        $all = DashboardWidgetRegistry::get_all();
        $this->assertSame( array( 'organization' ), $all['test_widget']['roles'] );
        $this->assertSame( 'edit_posts', $all['test_widget']['capability'] );
    }

    public function test_org_analytics_widget_fallback_for_non_capable_user(): void {
        $uid = self::factory()->user->create( array( 'role' => 'subscriber' ) );
        wp_set_current_user( $uid );

        $html = OrgAnalyticsWidget::render( $uid );
        $this->assertStringContainsString( 'notice-error', $html );
    }

    public function test_org_analytics_widget_renders_for_capable_user(): void {
        $uid = self::factory()->user->create( array( 'role' => 'organization' ) );
        wp_set_current_user( $uid );

        $html = OrgAnalyticsWidget::render( $uid );
        $this->assertStringNotContainsString( 'notice-error', $html );
        $this->assertStringContainsString( 'Basic traffic', $html );
    }

    /**
     * In preview mode, the widget must render as an empty string.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_org_analytics_widget_hidden_in_builder_preview(): void {
        // Belt + suspenders: constant AND filter so preview is guaranteed in this process.
        if ( ! defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
            define( 'IS_DASHBOARD_BUILDER_PREVIEW', true );
        }
        add_filter( 'ap_is_builder_preview', '__return_true' );

        $uid = self::factory()->user->create( array( 'role' => 'subscriber' ) );
        wp_set_current_user( $uid );

        $html = OrgAnalyticsWidget::render( $uid );

        remove_filter( 'ap_is_builder_preview', '__return_true' );
        $this->assertSame( '', $html );
    }

    public function test_visibility_page_reflects_saved_settings(): void {
        DashboardWidgetRegistry::register_widget(
            'test_widget',
            array(
                'label'    => 'Test Widget',
                'callback' => '__return_null',
                'roles'    => array( 'member' ),
            )
        );

        update_option(
            'artpulse_widget_roles',
            array(
                'test_widget' => array(
                    'roles'      => array( 'member' ),
                    'capability' => 'edit_posts',
                ),
            )
        );

        ob_start();
        ap_render_widget_visibility_page();
        $html = ob_get_clean();

        $this->assertStringContainsString( 'value="member" checked', $html );
        $this->assertStringContainsString( 'value="edit_posts"', $html );
    }

    public function test_widget_visibility_per_role(): void {
        $member = self::factory()->user->create( array( 'role' => 'member' ) );
        wp_set_current_user( $member );
        ob_start();
        DashboardWidgetRegistry::render_for_role( $member );
        $html = ob_get_clean();
        $this->assertStringContainsString( 'Events content.', $html );
        $this->assertStringNotContainsString( 'Example donations', $html );
        $this->assertStringContainsString( '<h2>Insights</h2>', $html );
        $this->assertStringNotContainsString( '<h2>Actions</h2>', $html );

        $sub = self::factory()->user->create( array( 'role' => 'subscriber' ) );
        wp_set_current_user( $sub );
        ob_start();
        DashboardWidgetRegistry::render_for_role( $sub );
        $html = ob_get_clean();
        $this->assertStringNotContainsString( 'Events content.', $html );
        $this->assertStringNotContainsString( 'Example donations', $html );
        $this->assertStringNotContainsString( 'Basic traffic', $html );

        $org_role = get_role( 'organization' );
        if ( $org_role ) {
            $org_role->add_cap( 'view_analytics' );
        }
        $org = self::factory()->user->create( array( 'role' => 'organization' ) );
        wp_set_current_user( $org );
        ob_start();
        DashboardWidgetRegistry::render_for_role( $org );
        $html = ob_get_clean();
        $this->assertStringContainsString( 'Example donations', $html );
        $this->assertStringContainsString( 'Basic traffic', $html );
        $this->assertStringContainsString( '<h2>Insights</h2>', $html );
        $this->assertStringContainsString( '<h2>Actions</h2>', $html );
    }

    public function test_donations_template_override_loaded(): void {
        $dir = sys_get_temp_dir() . '/ap-theme-' . wp_generate_password( 8, false, false );
        mkdir( $dir . '/templates/widgets', 0777, true );
        file_put_contents( $dir . '/templates/widgets/donations.php', '<p>override</p>' );
        $filter = static function () use ( $dir ) {
            return $dir;
        };
        add_filter( 'stylesheet_directory', $filter );

        $uid = self::factory()->user->create( array( 'role' => 'organization' ) );
        wp_set_current_user( $uid );

        $html = DonationsWidget::render( $uid );

        remove_filter( 'stylesheet_directory', $filter );
        rm_rf( $dir );

        $this->assertStringContainsString( 'override', $html );
    }

    public function test_donations_template_falls_back_when_missing(): void {
        $uid = self::factory()->user->create( array( 'role' => 'organization' ) );
        wp_set_current_user( $uid );

        $html = DonationsWidget::render( $uid );

        $this->assertStringContainsString( 'Example donations', $html );
    }
}
