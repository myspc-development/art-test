<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
use ArtPulse\Admin\SpotlightManager;

$category   = $args['category'] ?? null;
$spotlights = [];
if (!defined('IS_DASHBOARD_BUILDER_PREVIEW')) {
    $spotlights = SpotlightManager::get_dashboard_spotlights($args['role'] ?? 'member', $category);
} else {
    echo '<p class="notice">' . esc_html__( 'Preview mode — dynamic content hidden', 'artpulse' ) . '</p>';
    return;
}

if (empty($spotlights)) {
    echo '<p class="ap-empty-state">' . esc_html__('No featured content available right now.', 'artpulse') . '</p>';
    return;
}
?>

<div id="ap-spotlight-dashboard" class="ap-card" role="region" aria-labelledby="ap-spotlight-dashboard-title">
  <h2 id="ap-spotlight-dashboard-title" class="ap-card__title">🌟 <?= __('Featured for You', 'artpulse') ?></h2>
  <div>
    <?php foreach ($spotlights as $post): ?>
      <div class="ap-spotlight-card">
        <?php
        $terms = get_the_terms($post, 'spotlight_category');
        if (!empty($terms)) {
            echo '<span class="spotlight-tag">' . esc_html($terms[0]->name) . '</span>';
        }
        ?>
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
