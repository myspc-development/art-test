<?php
/**
 * Spotlight widget with optional pinning (Sprint 2)
 */
use WP_Query;

$role     = $args['role'] ?? 'member';
$category = $args['category'] ?? null;

$query_args = [
    'post_type'      => 'spotlight',
    'posts_per_page' => 5,
    'meta_key'       => 'is_pinned',
    'orderby'        => [ 'meta_value_num' => 'DESC', 'date' => 'DESC' ],
];

if ($category) {
    $query_args['tax_query'][] = [
        'taxonomy' => 'spotlight_category',
        'field'    => 'slug',
        'terms'    => $category,
    ];
}

$spot_query = new WP_Query($query_args);
?>
<div class="ap-widget">
  <div class="ap-widget-header">🌟 <?php _e('Spotlights','artpulse'); ?></div>
  <div class="ap-widget-body">
    <?php if ($spot_query->have_posts()) : ?>
      <?php while ($spot_query->have_posts()) : $spot_query->the_post(); ?>
        <div class="ap-spotlight-card<?php echo get_post_meta(get_the_ID(), 'is_pinned', true) ? ' is-pinned' : ''; ?>">
          <?php
          $terms = get_the_terms(get_the_ID(), 'spotlight_category');
          if ($terms) {
              $t = $terms[0];
              echo '<span class="spotlight-cat badge-' . esc_attr($t->slug) . '">' . esc_html($t->name) . '</span>';
          }
          ?>
          <strong><?php the_title(); ?></strong>
          <p><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
          <a href="<?php the_permalink(); ?>" class="button small"><?php esc_html_e('View', 'artpulse'); ?></a>
        </div>
      <?php endwhile; wp_reset_postdata(); ?>
    <?php else : ?>
      <p><?php esc_html_e('No spotlights found.', 'artpulse'); ?></p>
    <?php endif; ?>
  </div>
</div>
