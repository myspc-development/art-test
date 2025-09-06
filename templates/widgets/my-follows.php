<?php
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}
if ( ! user_can( get_current_user_id(), 'read' ) ) {
	return;
}
$args    = ap_template_context( $args ?? array(), array( 'visible' => true ) );
$visible = $args['visible'] ?? true;
/** Dashboard widget: My Follows */

$fallback_items = array();
$records        = \ArtPulse\Community\FollowManager::get_user_follows( get_current_user_id() );
if ( $records ) {
	foreach ( $records as $record ) {
		if ( $record->object_type === 'user' ) {
			$user = get_user_by( 'id', $record->object_id );
			if ( $user ) {
				$fallback_items[] = array(
					'url'  => get_author_posts_url( $user->ID ),
					'name' => $user->display_name,
				);
			}
		} elseif ( post_type_exists( $record->object_type ) ) {
			$post = get_post( $record->object_id );
			if ( $post ) {
				$fallback_items[] = array(
					'url'  => get_permalink( $post ),
					'name' => get_the_title( $post ),
				);
			}
		}
		if ( count( $fallback_items ) >= 5 ) {
			break;
		}
	}
}
?>
<div id="my-follows" class="ap-card" role="region" aria-labelledby="my-follows-title" data-slug="widget_my_follows" data-widget="my_follows" <?php echo $visible ? '' : 'hidden'; ?>>
	<h2 id="my-follows-title" class="ap-card__title"><?php esc_html_e( 'My Follows', 'artpulse' ); ?></h2>
	<div class="ap-my-follows">
		<div class="ap-directory-results">
			<?php if ( $fallback_items ) : ?>
				<ul class="ap-follows-list">
					<?php foreach ( $fallback_items as $item ) : ?>
						<li><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['name'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p><?php esc_html_e( 'You are not following any artists or events.', 'artpulse' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</div>
