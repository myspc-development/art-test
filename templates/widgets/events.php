<?php
extract(ap_template_context($args ?? [], ['visible' => true]));
/**
 * Dashboard widget: Upcoming events.
 */
?>
<section id="events" class="ap-dashboard-section dashboard-card" data-widget="events" <?php echo $visible ? '' : 'style="display:none"'; ?>>
    <h2><?php esc_html_e('Upcoming Events','artpulse'); ?></h2>
    <div id="ap-events-feed"></div>
    <button class="ap-widget-settings-btn ap-form-button nectar-button" data-widget-settings="events"><?php esc_html_e('Settings', 'artpulse'); ?></button>
</section>
