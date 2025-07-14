<?php
extract(ap_template_context($args ?? [], ['visible' => true]));
/**
 * Dashboard widget: RSVPs.
 */
?>
<section id="rsvps" class="ap-dashboard-section dashboard-card" data-widget="rsvps" <?php echo $visible ? '' : 'style="display:none"'; ?>>
    <h2><?php esc_html_e('My RSVPs','artpulse'); ?></h2>
    <div id="ap-rsvp-events"></div>
    <button class="ap-widget-settings-btn ap-form-button nectar-button" data-widget-settings="rsvps"><?php esc_html_e('Settings', 'artpulse'); ?></button>
</section>
