<?php
/**
 * Dashboard widget: Notifications.
 */
?>
<div class="dashboard-card" data-widget="notifications" <?php echo $visible ? '' : 'style="display:none"'; ?>>
    <h2 id="notifications"><?php esc_html_e('Notifications','artpulse'); ?></h2>
    <div id="ap-dashboard-notifications"></div>
    <button class="ap-widget-settings-btn ap-form-button nectar-button" data-widget-settings="notifications"><?php esc_html_e('Settings', 'artpulse'); ?></button>
</div>
