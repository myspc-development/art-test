<?php
use ArtPulse\Services\RecommendationService;
$user_id = get_current_user_id();
$items   = RecommendationService::get_for_user($user_id);
?>
<div class="ap-widget" id="ap-widget-for-you">
  <div class="ap-widget-header">🎯 <?php _e('For You','artpulse'); ?></div>
  <div class="ap-widget-body">
    <?php if ($items): ?>
      <ul>
        <?php foreach ($items as $item): ?>
          <li><a href="<?php echo esc_url($item['link']); ?>"><?php echo esc_html($item['title']); ?></a></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p><?php esc_html_e('No recommendations found.','artpulse'); ?></p>
    <?php endif; ?>
  </div>
</div>
