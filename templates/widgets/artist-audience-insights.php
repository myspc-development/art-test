<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
extract(ap_template_context($args ?? [], ['visible' => true]));
?>
<div id="artist-audience-insights" class="ap-card" role="region" aria-labelledby="artist-audience-insights-title" data-widget="artist-audience-insights" <?php echo $visible ? '' : 'hidden'; ?>>
  <h2 id="artist-audience-insights-title" class="ap-card__title"><?php esc_html_e('Audience Insights', 'artpulse'); ?></h2>
  <div class="ap-react-widget" data-widget-id="artist_audience_insights"></div>
</div>
