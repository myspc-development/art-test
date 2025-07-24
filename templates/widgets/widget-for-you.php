<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
use ArtPulse\Services\RecommendationService;
$user_id = get_current_user_id();
$items   = RecommendationService::get_for_user($user_id);
?>
<div id="ap-widget-for-you" class="ap-card" role="region" aria-labelledby="ap-widget-for-you-title">
  <h2 id="ap-widget-for-you-title" class="ap-card__title">🎯 <?php _e('For You','artpulse'); ?></h2>
  <div>
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
