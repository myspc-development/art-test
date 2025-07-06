<?php
/**
 * Dashboard widget: Messages.
 */
?>
<div class="dashboard-card" data-widget="messages">
    <h2 id="messages"><?php esc_html_e('Messages','artpulse'); ?></h2>
    <?php echo do_shortcode('[ap_messages]'); ?>
</div>
