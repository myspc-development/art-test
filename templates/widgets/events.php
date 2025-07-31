<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
extract(ap_template_context($args ?? [], ['visible' => true]));
/**
 * Dashboard widget: Upcoming events.
 */
?>
<section id="events" class="ap-card" role="region" aria-labelledby="events-title" data-widget="widget_events" <?php echo $visible ? '' : 'hidden'; ?>>
    <h2 id="events-title" class="ap-card__title"><?php esc_html_e('Upcoming Events','artpulse'); ?></h2>
    <div id="ap-events-feed"></div>
    <button class="ap-widget-settings-btn ap-form-button nectar-button" data-widget-settings="events"><?php esc_html_e('Settings', 'artpulse'); ?></button>
</section>
