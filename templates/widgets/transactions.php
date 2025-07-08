<?php
/**
 * Dashboard widget: Transactions.
 */
?>
<div class="dashboard-card" data-widget="transactions" <?php echo $visible ? '' : 'style="display:none"'; ?>>
    <h2 id="transactions"><?php esc_html_e('Recent Transactions','artpulse'); ?></h2>
    <div id="ap-transactions"></div>
</div>
