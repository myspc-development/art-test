<?php
/**
 * Dashboard widget: Transactions.
 */
?>
<section id="transactions" class="ap-dashboard-section dashboard-card" data-widget="transactions" <?php echo $visible ? '' : 'style="display:none"'; ?>>
    <h2><?php esc_html_e('Recent Transactions','artpulse'); ?></h2>
    <div id="ap-transactions"></div>
</section>
