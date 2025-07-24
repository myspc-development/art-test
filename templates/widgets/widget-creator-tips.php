<?php
$user_id = get_current_user_id();
$rsvp_count = 0;
$donation = '';
$badges   = [];
if (!defined('IS_DASHBOARD_BUILDER_PREVIEW')) {
    $rsvps   = get_user_meta($user_id, 'ap_rsvp_events', true);
    $rsvp_count = is_array($rsvps) ? count($rsvps) : 0;
    $donation = get_user_meta($user_id, 'ap_donation_link', true);
    $badges   = \ArtPulse\Core\UserDashboardManager::getBadges($user_id);
} else {
    echo '<p class="notice">Preview mode — dynamic content hidden</p>';
    return;
}

$tips = [];
if ($rsvp_count < 10) {
    $tips[] = __('Promote your events to boost RSVPs.', 'artpulse');
}
if (!$donation) {
    $tips[] = __('Add a donation link to receive support.', 'artpulse');
}
if (empty($badges)) {
    $tips[] = __('Engage with the community to earn badges.', 'artpulse');
}
?>
<div id="ap-widget-creator-tips" class="ap-card" role="region" aria-labelledby="ap-widget-creator-tips-title">
  <h2 id="ap-widget-creator-tips-title" class="ap-card__title">💡 <?php esc_html_e('Creator Tips', 'artpulse'); ?></h2>
  <?php if ($tips): ?>
    <ul class="list-disc ml-4">
      <?php foreach ($tips as $tip): ?>
        <li><?php echo esc_html($tip); ?></li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p><?php esc_html_e('Keep up the great work!', 'artpulse'); ?></p>
  <?php endif; ?>
</div>
