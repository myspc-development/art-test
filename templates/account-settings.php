<div class="ap-account-settings">
    <h2><?php esc_html_e('Account Settings', 'artpulse'); ?></h2>
    <form id="ap-notification-prefs" class="ap-form-container">
        <label>
            <input type="checkbox" name="email" <?php checked($email); ?>>
            <?php esc_html_e('Email Notifications', 'artpulse'); ?>
        </label>
        <label>
            <input type="checkbox" name="push" <?php checked($push); ?>>
            <?php esc_html_e('Push Notifications', 'artpulse'); ?>
        </label>
        <label>
            <input type="checkbox" name="sms" <?php checked($sms); ?>>
            <?php esc_html_e('SMS Notifications', 'artpulse'); ?>
        </label>
        <button type="submit" class="ap-form-button nectar-button"><?php esc_html_e('Save', 'artpulse'); ?></button>
    </form>
    <div id="ap-notification-status" class="ap-form-messages" role="status" aria-live="polite"></div>
</div>
