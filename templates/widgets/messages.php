<?php
/**
 * Dashboard widget: Messages.
 */
?>
<div class="dashboard-card" data-widget="messages">
    <h2 id="messages"><?php esc_html_e('Messages','artpulse'); ?></h2>
    <?php echo do_shortcode('[ap_messages]'); ?>
    <button class="ap-widget-settings-btn ap-form-button nectar-button" data-widget-settings="messages"><?php esc_html_e('Settings', 'artpulse'); ?></button>
</div>
