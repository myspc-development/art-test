<?php
$visible = $visible ?? true;
/**
 * Dashboard widget: Upgrade account.
 */
?>
<section id="upgrade" class="ap-dashboard-section dashboard-card" data-widget="upgrade" <?php echo $visible ? '' : 'style="display:none"'; ?>>
    <h2><?php esc_html_e('Upgrade Your Account','artpulse'); ?></h2>
    <div id="ap-upgrade-options"></div>
    <?php if (!empty($show_forms)) : ?>
    <div class="ap-dashboard-forms">
        <?php echo $artist_form ?? ''; ?>
        <?php echo $org_form ?? ''; ?>
    </div>
    <?php endif; ?>
    <button class="ap-widget-settings-btn ap-form-button nectar-button" data-widget-settings="upgrade"><?php esc_html_e('Settings', 'artpulse'); ?></button>
</section>
