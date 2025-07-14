<?php
use ArtPulse\Frontend\EventRsvpHandler;

$rsvp_data = EventRsvpHandler::get_rsvp_summary_for_user(get_current_user_id());
?>

<div class="ap-widget notice notice-info p-4 rounded">
  <div class="ap-widget-header">📅 <?= __('RSVP Stats', 'artpulse') ?></div>
  <div class="ap-widget-body">
    <p><strong><?= $rsvp_data['going'] ?></strong> <?= esc_html__( 'Going', 'artpulse' ); ?></p>
    <p><strong><?= $rsvp_data['interested'] ?></strong> <?= esc_html__( 'Interested', 'artpulse' ); ?></p>

    <?php if (!empty($rsvp_data['trend'])): ?>
      <div class="ap-rsvp-chart" data-chart="<?= esc_attr(json_encode($rsvp_data['trend'])) ?>"></div>
    <?php endif; ?>

    <a href="/dashboard/events" class="button small"><?= __('Manage Events', 'artpulse') ?></a>
  </div>
</div>
