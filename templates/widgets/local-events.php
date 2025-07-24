<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
extract(ap_template_context($args ?? [], ['visible' => true]));
/**
 * Dashboard widget: Local events.
 */
?>
<div id="local-events" class="ap-card" role="region" aria-labelledby="local-events-title" data-widget="local-events" <?php echo $visible ? '' : 'hidden'; ?>>
    <h2 id="local-events-title" class="ap-card__title"><?php esc_html_e('Events Near You','artpulse'); ?></h2>
    <div id="ap-local-events"></div>
    <button class="ap-widget-settings-btn ap-form-button nectar-button" data-widget-settings="local-events"><?php esc_html_e('Settings', 'artpulse'); ?></button>
</div>
