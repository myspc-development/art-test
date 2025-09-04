<?php
namespace ArtPulse;

class Widgets {
	public static function render_welcome_box(): string {
                $user = wp_get_current_user();
                $name = $user->display_name ?: $user->user_login;
                $text = sprintf( esc_html__( 'Welcome back, %1$s!', 'artpulse' ), esc_html( $name ) );
                return '<div data-widget-id="welcome_box"><p>' . $text . '</p></div>';
	}

	public static function render_portfolio_box(): string {
                $count = wp_count_posts( 'artpulse_artwork' )->publish ?? 0;
                $text  = sprintf( esc_html__( 'You have %1$d published artworks.', 'artpulse' ), $count );
                return '<div data-widget-id="portfolio_preview"><p>' . $text . '</p></div>';
	}

	public static function render_org_insights_box(): string {
		$metrics = \ArtPulse\Rest\OrgAnalyticsController::get_metrics( new \WP_REST_Request() );
		if ( $metrics instanceof \WP_REST_Response ) {
			$metrics = $metrics->get_data();
		}
		$events   = $metrics['event_count'] ?? 0;
		$artworks = $metrics['artwork_count'] ?? 0;
		$text     = sprintf( esc_html__( '%1$d events · %2$d artworks', 'artpulse' ), $events, $artworks );
		return '<div data-widget-id="org_insights"><p>' . esc_html( $text ) . '</p></div>';
	}
}
