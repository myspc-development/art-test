<?php
/**
 * Dashboard widget: Next Payment.
 */
use ArtPulse\Core\Plugin;
?>
<div class="dashboard-card" data-widget="next-payment">
    <h2 id="next-payment"><?php esc_html_e('Next Payment','artpulse'); ?></h2>
    <div id="ap-next-payment"></div>
    <p><a href="<?php echo esc_url(Plugin::get_payouts_url()); ?>" class="nectar-button small"><?php esc_html_e('View Payouts','artpulse'); ?></a></p>
</div>
