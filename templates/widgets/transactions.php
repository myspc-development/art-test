<?php
extract(ap_template_context($args ?? [], ['visible' => true]));
/**
 * Dashboard widget: Transactions.
 */
?>
<div id="transactions" class="ap-card" role="region" aria-labelledby="transactions-title" data-widget="transactions" <?php echo $visible ? '' : 'hidden'; ?>>
    <h2 id="transactions-title" class="ap-card__title"><?php esc_html_e('Recent Transactions','artpulse'); ?></h2>
    <div id="ap-transactions"></div>
</div>
