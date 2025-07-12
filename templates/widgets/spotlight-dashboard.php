<?php
use ArtPulse\Admin\SpotlightManager;

$spotlights = SpotlightManager::get_dashboard_spotlights($args['role'] ?? 'member');

if (empty($spotlights)) {
    echo '<p class="ap-empty-state">' . esc_html__('No featured content available right now.', 'artpulse') . '</p>';
    return;
}
?>

<div class="ap-widget">
  <div class="ap-widget-header">🌟 <?= __('Featured for You', 'artpulse') ?></div>
  <div class="ap-widget-body">
    <?php foreach ($spotlights as $post): ?>
      <div class="ap-spotlight-card">
        <strong><?= esc_html($post->post_title) ?></strong><br>
        <p><?= wp_trim_words($post->post_content, 20); ?></p>
        <?php
        $cta_text   = get_post_meta($post->ID, 'cta_text', true);
        $cta_url    = get_post_meta($post->ID, 'cta_url', true);
        $cta_target = get_post_meta($post->ID, 'cta_target', true);
        if ($cta_text && $cta_url): ?>
          <a href="<?= esc_url($cta_url) ?>" class="button small cta-button" <?= $cta_target === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
            <?= esc_html($cta_text) ?>
          </a>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>
