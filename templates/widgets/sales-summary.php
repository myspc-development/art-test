<?php
extract(ap_template_context($args ?? [], ['visible' => true]));
/**
 * Dashboard widget: Sales Summary.
 */
?>
<section id="sales-summary" class="ap-dashboard-section dashboard-card" data-widget="sales_summary" <?php echo $visible ? '' : 'style="display:none"'; ?>>
    <h2><?php esc_html_e('Sales Summary','artpulse'); ?></h2>
    <div id="ap-sales-summary"></div>
</section>
