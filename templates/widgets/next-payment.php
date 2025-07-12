<?php
/**
 * Dashboard widget: Next Payment.
 */
use ArtPulse\Core\Plugin;
?>
<section id="next-payment" class="ap-dashboard-section dashboard-card" data-widget="next-payment" <?php echo $visible ? '' : 'style="display:none"'; ?>>
    <h2><?php esc_html_e('Next Payment','artpulse'); ?></h2>
    <div id="ap-next-payment"></div>
    <p><a href="<?php echo esc_url(Plugin::get_payouts_url()); ?>" class="nectar-button small"><?php esc_html_e('View Payouts','artpulse'); ?></a></p>
</section>
