<?php
/**
 * Dashboard widget: Upgrade account.
 */
?>
<div class="dashboard-card" data-widget="upgrade">
    <h2 id="upgrade"><?php esc_html_e('Upgrade Your Account','artpulse'); ?></h2>
    <div id="ap-upgrade-options"></div>
    <?php if (!empty($show_forms)) : ?>
    <div class="ap-dashboard-forms">
        <?php echo $artist_form ?? ''; ?>
        <?php echo $org_form ?? ''; ?>
    </div>
    <?php endif; ?>
</div>
