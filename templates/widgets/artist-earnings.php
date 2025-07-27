<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
extract(ap_template_context($args ?? [], ['visible' => true]));
?>
<div id="artist-earnings" class="ap-card" role="region" aria-labelledby="artist-earnings-title" data-widget="artist-earnings" <?php echo $visible ? '' : 'hidden'; ?>>
  <h2 id="artist-earnings-title" class="ap-card__title"><?php esc_html_e('Earnings Summary', 'artpulse'); ?></h2>
  <div class="ap-react-widget" data-widget-id="artist_earnings_summary"></div>
</div>
