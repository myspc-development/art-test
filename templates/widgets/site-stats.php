<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
extract(ap_template_context($args ?? [], ['visible' => true]));
/**
 * Dashboard widget: Site Stats.
 */
?>
<div id="site-stats" class="ap-card" role="region" aria-labelledby="site-stats-title" data-widget="site_stats" <?php echo $visible ? '' : 'hidden'; ?>>
    <h2 id="site-stats-title" class="ap-card__title"><?php esc_html_e('Site Stats','artpulse'); ?></h2>
    <div id="ap-site-stats"></div>
</div>
