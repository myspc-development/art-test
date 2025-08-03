<?php
if (!defined('ABSPATH')) { exit; }

use ArtPulse\Core\DashboardWidgetInterface;
use ArtPulse\Core\DashboardWidgetRegistry;

/**
 * Example widget boilerplate.
 *
 * Provides a starting point for creating dashboard widgets.
 */
class ExampleWidget implements DashboardWidgetInterface {
    /**
     * Register the widget with the dashboard.
     */
    public static function register(): void {
        DashboardWidgetRegistry::register(
            self::id(),
            self::label(),
            self::icon(),
            self::description(),
            [ self::class, 'render' ],
            [ 'roles' => self::roles() ]
        );
    }

    /** Widget slug used as unique identifier. */
    public static function id(): string {
        return 'example_widget';
    }

    /** Human readable widget title. */
    public static function label(): string {
        return __( 'Example Widget', 'artpulse' );
    }

    /**
     * Roles that may view the widget.
     *
     * @return array
     */
    public static function roles(): array {
        return [ 'member' ];
    }

    /** Short description shown in UI. */
    public static function description(): string {
        return __( 'A starting point for custom widgets.', 'artpulse' );
    }

    /** Dashicon name for the widget. */
    public static function icon(): string {
        return 'smiley';
    }

    /**
     * Check if current user can view widget.
     */
    public static function can_view(): bool {
        return is_user_logged_in();
    }

    /**
     * Render the widget output.
     */
    public static function render(): string {
        if (!self::can_view()) {
            return '<div class="notice notice-error"><p>' . esc_html__( 'You do not have access.', 'artpulse' ) . '</p></div>';
        }

        ob_start();
        include __DIR__ . '/example-widget-template.php';
        return ob_get_clean();
    }
}

ExampleWidget::register();
