<?php
/**
 * Dashboard widget: Support History.
 */
?>
<div class="dashboard-card" data-widget="support-history" <?php echo $visible ? '' : 'style="display:none"'; ?>>
    <section id="support-history">
        <h2><?php esc_html_e('Support History','artpulse'); ?></h2>
        <div id="ap-support-history"></div>
    </section>
    <button class="ap-widget-settings-btn ap-form-button nectar-button" data-widget-settings="support-history"><?php esc_html_e('Settings', 'artpulse'); ?></button>
</div>
