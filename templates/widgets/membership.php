<?php
$visible = $visible ?? true;
/**
 * Dashboard widget: Membership.
 */
?>
<section id="membership" class="ap-dashboard-section dashboard-card" data-widget="membership" <?php echo $visible ? '' : 'style="display:none"'; ?>>
    <h2><?php esc_html_e('Subscription Status','artpulse'); ?></h2>
    <div id="ap-membership-info"></div>
    <?php if (!empty($badges)) : ?>
    <div class="ap-badges"></div>
    <?php endif; ?>
    <div id="ap-membership-actions"></div>
    <?php if (!empty($profile_edit_url)) : ?>
    <a class="ap-edit-profile-link ap-form-button nectar-button" href="<?php echo esc_url($profile_edit_url); ?>"><?php esc_html_e('Edit Profile', 'artpulse'); ?></a>
    <?php endif; ?>
    <button class="ap-widget-settings-btn ap-form-button nectar-button" data-widget-settings="membership"><?php esc_html_e('Settings', 'artpulse'); ?></button>
</section>
