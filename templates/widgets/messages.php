<?php
extract(ap_template_context($args ?? [], ['visible' => true]));
/**
 * Dashboard widget: Messages.
 */
?>
<section id="messages" class="ap-dashboard-section dashboard-card" data-widget="messages" <?php echo $visible ? '' : 'style="display:none"'; ?>>
    <h2><?php esc_html_e('Messages','artpulse'); ?></h2>
    <?php echo do_shortcode('[ap_messages]'); ?>
    <button class="ap-widget-settings-btn ap-form-button nectar-button" data-widget-settings="messages"><?php esc_html_e('Settings', 'artpulse'); ?></button>
</section>
