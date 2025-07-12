<?php
/**
 * Spotlight widget with optional pinning (Sprint 2)
 */
use ArtPulse\Admin\SpotlightManager;
$role  = $args['role'] ?? 'member';
$posts = SpotlightManager::get_dashboard_spotlights($role);
?>
<div class="ap-widget">
  <div class="ap-widget-header">🌟 <?php _e('Spotlights','artpulse'); ?></div>
  <div class="ap-widget-body">
    <?php foreach ($posts as $post): ?>
      <div class="ap-spotlight-card<?php echo get_post_meta($post->ID, 'is_pinned', true) ? ' is-pinned' : ''; ?>">
        <?php
        $terms = get_the_terms($post, 'spotlight_category');
        if ($terms) {
            $t = $terms[0];
            echo '<span class="spotlight-cat badge-' . esc_attr($t->slug) . '">' . esc_html($t->name) . '</span>';
        }
        ?>
        <strong><?php echo esc_html($post->post_title); ?></strong>
      </div>
    <?php endforeach; ?>
  </div>
</div>
