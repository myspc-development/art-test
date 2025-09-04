<?php
namespace ArtPulse\Widgets;

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Community\FavoritesManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}

/**
 * Wrapper widget for Favorites Overview.
 */

class FavoritesOverviewWidget {
	public static function register(): void {
		DashboardWidgetRegistry::register(
			self::id(),
			self::label(),
			self::icon(),
			self::description(),
			array( self::class, 'render' ),
			array( 'roles' => self::roles() )
		);

		// Legacy alias used in older configs.
		if ( ! DashboardWidgetRegistry::exists( 'widget_widget_favorites' ) ) {
			DashboardWidgetRegistry::register(
				'widget_widget_favorites',
                                sprintf( esc_html__( '%1$s (Legacy)', 'artpulse' ), self::label() ),
				self::icon(),
				self::description(),
				array( self::class, 'render' ),
				array( 'roles' => self::roles() )
			);
		}
	}

	public static function id(): string {
		return 'widget_favorites';
	}

	public static function label(): string {
		return esc_html__( 'Favorites Overview', 'artpulse' );
	}

	public static function roles(): array {
		return array( 'member' );
	}

	public static function description(): string {
		return esc_html__( 'Your favorite artists and works.', 'artpulse' );
	}

	public static function icon(): string {
		return 'heart';
	}

	public static function render( int $user_id = 0 ): string {
		$user_id = $user_id ?: get_current_user_id();
		if ( ! $user_id ) {
			return self::render_placeholder();
		}

		$favorites = FavoritesManager::get_user_favorites( $user_id, 'event' );

		if ( empty( $favorites ) ) {
			$content = '<p>' . esc_html__( 'No favorites yet', 'artpulse' ) . '</p>';
			return '<div class="inside">' . $content . '</div>';
		}

		$items = array();
		foreach ( $favorites as $fav ) {
			$post_id = (int) $fav->object_id;
			$url     = get_permalink( $post_id );
			$title   = get_the_title( $post_id );

			if ( $url && $title ) {
                                $items[] = sprintf( '<li><a href="%1$s">%2$s</a></li>', esc_url( $url ), esc_html( $title ) );
			}
		}

		if ( ! $items ) {
			$content = '<p>' . esc_html__( 'No favorites yet', 'artpulse' ) . '</p>';
		} else {
			$content = '<ul class="ap-favorites-overview">' . implode( '', $items ) . '</ul>';
		}

		return '<div class="inside">' . $content . '</div>';
	}

	public static function render_placeholder(): string {
		return '<div data-widget="' . esc_attr( self::id() ) . '" data-widget-id="' . esc_attr( self::id() ) . '" class="dashboard-widget"><div class="inside"><div class="ap-widget-placeholder">' .
			esc_html__( 'Favorites widget is under construction.', 'artpulse' ) .
			'</div></div></div>';
	}
}

FavoritesOverviewWidget::register();
