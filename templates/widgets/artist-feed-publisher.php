<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
extract(ap_template_context($args ?? [], ['visible' => true]));
?>
<div id="artist-feed-publisher" class="ap-card" role="region" aria-labelledby="artist-feed-publisher-title" data-widget="artist-feed-publisher" <?php echo $visible ? '' : 'hidden'; ?>>
  <h2 id="artist-feed-publisher-title" class="ap-card__title"><?php esc_html_e('Post & Engage', 'artpulse'); ?></h2>
  <div class="ap-react-widget" data-widget-id="artist_feed_publisher"></div>
</div>
