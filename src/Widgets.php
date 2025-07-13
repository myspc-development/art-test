<?php
namespace ArtPulse;

class Widgets {
    public static function render_welcome_box(): string {
        return '<p>' . esc_html__( 'Welcome to ArtPulse!', 'artpulse' ) . '</p>';
    }

    public static function render_portfolio_box(): string {
        return '<p>' . esc_html__( 'Portfolio preview coming soon.', 'artpulse' ) . '</p>';
    }

    public static function render_org_insights_box(): string {
        return '<p>' . esc_html__( 'Organization insights placeholder.', 'artpulse' ) . '</p>';
    }
}
