<?php
/**
 * Dashboard widget: RSVPs.
 */
?>
<div class="dashboard-card" data-widget="rsvps" <?php echo $visible ? '' : 'style="display:none"'; ?>>
    <h2 id="rsvps"><?php esc_html_e('My RSVPs','artpulse'); ?></h2>
    <div id="ap-rsvp-events"></div>
    <button class="ap-widget-settings-btn ap-form-button nectar-button" data-widget-settings="rsvps"><?php esc_html_e('Settings', 'artpulse'); ?></button>
</div>
