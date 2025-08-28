<?php
namespace ArtPulse\Frontend;

class RestListShortcodes {

	public static function register(): void {
		\ArtPulse\Core\ShortcodeRegistry::register( 'ap_recommendations', 'Recommendations', array( self::class, 'render_recommendations' ) );
		\ArtPulse\Core\ShortcodeRegistry::register( 'ap_collection', 'Collection Detail', array( self::class, 'render_collection' ) );
	}

	public static function render_recommendations( $atts ): string {
		$atts = shortcode_atts(
			array(
				'type'  => 'event',
				'limit' => 6,
			),
			$atts,
			'ap_recommendations'
		);

		return sprintf(
			'<div class="ap-rest-list ap-recommendations" data-type="%s" data-limit="%d"></div>',
			esc_attr( $atts['type'] ),
			intval( $atts['limit'] )
		);
	}

	public static function render_collection( $atts ): string {
		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts,
			'ap_collection'
		);

		$id = intval( $atts['id'] );
		if ( ! $id ) {
			return '';
		}

		$post = get_post( $id );
		if ( ! $post || $post->post_type !== 'ap_collection' ) {
			return '';
		}

		ob_start();
		?>
		<div class="ap-collection-detail">
			<h2 class="ap-collection-title"><?php echo esc_html( $post->post_title ); ?></h2>
			<div class="ap-rest-list ap-collection" data-id="<?php echo esc_attr( $id ); ?>"></div>
		</div>
		<?php
		return ob_get_clean();
	}
}
