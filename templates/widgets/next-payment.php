<?php
extract(ap_template_context($args ?? [], ['visible' => true]));
/**
 * Dashboard widget: Next Payment.
 */
use ArtPulse\Core\Plugin;
?>
<div id="next-payment" class="ap-card" role="region" aria-labelledby="next-payment-title" data-widget="next-payment" <?php echo $visible ? '' : 'hidden'; ?>>
    <h2 id="next-payment-title" class="ap-card__title"><?php esc_html_e('Next Payment','artpulse'); ?></h2>
    <div id="ap-next-payment"></div>
    <p><a href="<?php echo esc_url(Plugin::get_payouts_url()); ?>" class="nectar-button small"><?php esc_html_e('View Payouts','artpulse'); ?></a></p>
</div>
