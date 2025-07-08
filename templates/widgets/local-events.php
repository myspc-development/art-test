<?php
/**
 * Dashboard widget: Local events.
 */
?>
<div class="dashboard-card" data-widget="local-events" <?php echo $visible ? '' : 'style="display:none"'; ?>>
    <h2 id="local-events"><?php esc_html_e('Events Near You','artpulse'); ?></h2>
    <div id="ap-local-events"></div>
    <button class="ap-widget-settings-btn ap-form-button nectar-button" data-widget-settings="local-events"><?php esc_html_e('Settings', 'artpulse'); ?></button>
</div>
