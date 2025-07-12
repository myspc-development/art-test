<?php
use ArtPulse\Admin\SpotlightManager;
use ArtPulse\Core\DashboardController;

$user_id = get_current_user_id();
$role    = DashboardController::get_role($user_id);

$spotlights = SpotlightManager::get_dashboard_spotlights($role);
$city      = get_user_meta($user_id, 'ap_city', true);
$event_args = [
    'post_type'      => 'artpulse_event',
    'numberposts'    => 3,
];
if ($city) {
    $event_args['meta_query'][] = [
        'key'   => 'event_city',
        'value' => $city,
    ];
}
$events = get_posts($event_args);
?>
<div class="ap-widget">
  <div class="ap-widget-header">🎯 <?php _e('For You','artpulse'); ?></div>
  <div class="ap-widget-body">
    <?php if ($spotlights): ?>
      <h4><?php _e('Spotlights','artpulse'); ?></h4>
      <ul>
        <?php foreach ($spotlights as $p): ?>
          <li><a href="<?php echo esc_url(get_permalink($p)); ?>"><?php echo esc_html($p->post_title); ?></a></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <?php if ($events): ?>
      <h4><?php _e('Events','artpulse'); ?></h4>
      <ul>
        <?php foreach ($events as $e): ?>
          <li><a href="<?php echo esc_url(get_permalink($e)); ?>"><?php echo esc_html($e->post_title); ?></a></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>
