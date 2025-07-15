<?php
extract(ap_template_context($args ?? [], ['visible' => true]));
/**
 * Dashboard widget: Notifications.
 */
?>
<div id="notifications" class="ap-card" role="region" aria-labelledby="notifications-title" data-widget="notifications" <?php echo $visible ? '' : 'hidden'; ?>>
    <h2 id="notifications-title" class="ap-card__title"><?php esc_html_e('Notifications','artpulse'); ?></h2>
    <div id="ap-dashboard-notifications"></div>
    <button class="ap-widget-settings-btn ap-form-button nectar-button" data-widget-settings="notifications"><?php esc_html_e('Settings', 'artpulse'); ?></button>
</div>
