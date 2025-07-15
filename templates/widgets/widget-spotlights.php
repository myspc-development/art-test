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
<div id="ap-widget-spotlights" class="ap-card" role="region" aria-labelledby="ap-widget-spotlights-title">
  <h2 id="ap-widget-spotlights-title" class="ap-card__title">🌟 <?php _e('Spotlights','artpulse'); ?></h2>
  <div>
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
          <a href="<?php the_permalink(); ?>" class="button small ap-spotlight-view" data-spot-id="<?php echo get_the_ID(); ?>"><?php esc_html_e('View', 'artpulse'); ?></a>
          <div class="spotlight-share">
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" target="_blank" rel="noopener" class="share-facebook">FB</a>
            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>" target="_blank" rel="noopener" class="share-twitter">Tw</a>
            <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(get_permalink()); ?>" target="_blank" rel="noopener" class="share-linkedin">In</a>
            <a href="mailto:?subject=<?php echo rawurlencode(get_the_title()); ?>&body=<?php echo urlencode(get_permalink()); ?>" class="share-email">Email</a>
          </div>
        </div>
      <?php endwhile; wp_reset_postdata(); ?>
    <?php else : ?>
      <p><?php esc_html_e('No spotlights found.', 'artpulse'); ?></p>
    <?php endif; ?>
  </div>
</div>
<script>
document.querySelectorAll('.ap-spotlight-view').forEach(btn => {
  btn.addEventListener('click', () => {
    fetch('/wp-json/art/v1/spotlight/view', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: btn.dataset.spotId })
    });
  });
});
</script>
