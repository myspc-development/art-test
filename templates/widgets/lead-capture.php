<?php
$visible = $visible ?? true;
/**
 * Dashboard widget: Lead Capture.
 */
?>
<section id="lead-capture" class="ap-dashboard-section dashboard-card" data-widget="lead_capture" <?php echo $visible ? '' : 'style="display:none"'; ?>>
    <h2><?php esc_html_e('Lead Capture','artpulse'); ?></h2>
    <div id="ap-lead-capture"></div>
</section>
