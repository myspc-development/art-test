<?php
/**
 * Dashboard widget: Account Tools.
 */
?>
<div class="dashboard-card" data-widget="account-tools" <?php echo $visible ? '' : 'style="display:none"'; ?>>
    <h2 id="account-tools"><?php esc_html_e('Account Tools','artpulse'); ?></h2>
    <div id="ap-account-tools">
        <button id="ap-export-json" class="ap-form-button nectar-button"><?php esc_html_e('Export JSON','artpulse'); ?></button>
        <button id="ap-export-csv" class="ap-form-button nectar-button"><?php esc_html_e('Export CSV','artpulse'); ?></button>
        <button id="ap-delete-account" class="ap-form-button nectar-button"><?php esc_html_e('Delete Account','artpulse'); ?></button>
    </div>
    <button class="ap-widget-settings-btn ap-form-button nectar-button" data-widget-settings="account-tools"><?php esc_html_e('Settings', 'artpulse'); ?></button>
</div>
