<?php
use ArtPulse\Widgets\Member\ActivityFeedWidget;

$heading_id = sanitize_title( ActivityFeedWidget::id() ) . '-heading-' . uniqid();
?>
<section role="region" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>"
    data-widget="<?= esc_attr( ActivityFeedWidget::id() ); ?>"
    data-widget-id="<?= esc_attr( ActivityFeedWidget::id() ); ?>"
    class="ap-widget ap-<?= esc_attr( ActivityFeedWidget::id() ); ?>">
    <h2 id="<?php echo esc_attr( $heading_id ); ?>"><?php esc_html_e( 'Recent Activity', 'artpulse' ); ?></h2>
    <?php if ( ! empty( $logs ) ) : ?>
        <ul class="ap-activity-feed">
            <?php foreach ( $logs as $row ) : ?>
                <li><?= esc_html( $row->description ); ?> <em><?= esc_html( date_i18n( get_option( 'date_format' ) . ' H:i', strtotime( $row->logged_at ) ) ); ?></em></li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <p><?= esc_html__( 'No recent activity.', 'artpulse' ); ?></p>
    <?php endif; ?>
</section>

