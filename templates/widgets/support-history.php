<?php
extract(ap_template_context($args ?? [], ['visible' => true]));
/**
 * Dashboard widget: Support History.
 */
?>
<section id="support-history" class="ap-dashboard-section dashboard-card" data-widget="support-history" <?php echo $visible ? '' : 'style=\"display:none\"'; ?>>
    <h2><?php esc_html_e('Support History','artpulse'); ?></h2>
    <div id="ap-support-history"></div>
    <button class="ap-widget-settings-btn ap-form-button nectar-button" data-widget-settings="support-history"><?php esc_html_e('Settings', 'artpulse'); ?></button>
</section>
