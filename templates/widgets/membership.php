<?php
/**
 * Dashboard widget: Membership.
 */
?>
<div class="dashboard-card" data-widget="membership">
    <h2 id="membership"><?php esc_html_e('Subscription Status','artpulse'); ?></h2>
    <div id="ap-membership-info"></div>
    <?php if (!empty($badges)) : ?>
    <div class="ap-badges"></div>
    <?php endif; ?>
    <div id="ap-membership-actions"></div>
    <?php if (!empty($profile_edit_url)) : ?>
    <a class="ap-edit-profile-link ap-form-button nectar-button" href="<?php echo esc_url($profile_edit_url); ?>"><?php esc_html_e('Edit Profile', 'artpulse'); ?></a>
    <?php endif; ?>
    <button class="ap-widget-settings-btn ap-form-button nectar-button" data-widget-settings="membership"><?php esc_html_e('Settings', 'artpulse'); ?></button>
</div>
