<?php
namespace ArtPulse\Frontend;

use ArtPulse\Core\CompetitionEntryManager;

class CompetitionDashboardShortcode {

	public static function register(): void {
		\ArtPulse\Core\ShortcodeRegistry::register( 'ap_competition_dashboard', 'Competition Dashboard', array( self::class, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_styles' ) );
	}

	public static function enqueue_styles(): void {
		if ( function_exists( 'ap_enqueue_global_styles' ) ) {
			ap_enqueue_global_styles();
		}
	}

	public static function render(): string {
		$now  = current_time( 'mysql' );
		$open = get_posts(
			array(
				'post_type'   => 'ap_competition',
				'numberposts' => -1,
				'meta_query'  => array(
					array(
						'key'     => 'competition_deadline',
						'value'   => $now,
						'compare' => '>=',
					),
				),
			)
		);

		$closed = get_posts(
			array(
				'post_type'   => 'ap_competition',
				'numberposts' => -1,
				'meta_query'  => array(
					array(
						'key'     => 'competition_deadline',
						'value'   => $now,
						'compare' => '<',
					),
				),
			)
		);

		ob_start();
		echo '<div class="ap-competition-dashboard">';
		echo '<h2 class="ap-card__title">' . esc_html__( 'Open Competitions', 'artpulse' ) . '</h2><ul>';
		foreach ( $open as $c ) {
			echo '<li><a href="' . get_permalink( $c ) . '">' . esc_html( $c->post_title ) . '</a></li>';
		}
		echo '</ul><h2 class="ap-card__title">' . esc_html__( 'Results', 'artpulse' ) . '</h2><ul>';
		foreach ( $closed as $c ) {
			echo '<li>' . esc_html( $c->post_title ) . '<ul>';
			$entries = CompetitionEntryManager::get_entries( $c->ID );
			foreach ( $entries as $e ) {
				$art = get_post( $e['artwork_id'] );
				if ( $art ) {
					echo '<li>' . esc_html( $art->post_title ) . ' - ' . intval( $e['votes'] ) . '</li>';
				}
			}
			echo '</ul></li>';
		}
		echo '</ul></div>';
		return ob_get_clean();
	}
}
