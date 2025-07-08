<?php
use ArtPulse\Community\FavoritesManager;
/**
 * Member dashboard template.
 */
?>
<a href="#main-content" class="skip-link"><?php esc_html_e('Skip to main content', 'artpulse'); ?></a>
<div class="dashboard-wrapper">
    <aside class="dashboard-sidebar">
        <ul>
            <li><a href="#membership"><span class="dashicons dashicons-admin-users"></span><?php esc_html_e('Membership', 'artpulse'); ?></a></li>
            <li><a href="#upgrade"><span class="dashicons dashicons-star-filled"></span><?php esc_html_e('Upgrade', 'artpulse'); ?></a></li>
            <li><a href="#local-events"><span class="dashicons dashicons-location-alt"></span><?php esc_html_e('Local Events', 'artpulse'); ?></a></li>
            <li><a href="#favorites"><span class="dashicons dashicons-heart"></span><?php esc_html_e('Favorites', 'artpulse'); ?></a></li>
            <li><a href="#events"><span class="dashicons dashicons-calendar"></span><?php esc_html_e('Events', 'artpulse'); ?></a></li>
            <li><a href="#messages"><span class="dashicons dashicons-email"></span><?php esc_html_e('Messages', 'artpulse'); ?></a></li>
            <li><a href="#account-tools"><span class="dashicons dashicons-download"></span><?php esc_html_e('Account', 'artpulse'); ?></a></li>
            <?php if ( $show_notifications ) : ?>
            <li><a href="#notifications"><span class="dashicons dashicons-megaphone"></span><?php esc_html_e('Notifications', 'artpulse'); ?></a></li>
            <?php endif; ?>
        </ul>
    </aside>
    <main id="main-content">
    <div class="dashboard-main">
    <?php if ( ! get_user_meta( get_current_user_id(), 'ap_onboarding_completed', true ) ) : ?>
    <div id="ap-onboarding-banner" class="ap-onboarding-banner">
        <span><?php esc_html_e( 'Get started with a quick tour.', 'artpulse' ); ?></span>
        <div>
            <button id="ap-start-tour" class="ap-form-button"><?php esc_html_e( 'Start Tour', 'artpulse' ); ?></button>
            <button id="ap-dismiss-tour" class="ap-form-button"><?php esc_html_e( 'Dismiss', 'artpulse' ); ?></button>
        </div>
    </div>
    <?php endif; ?>

    <div id="ap-layout-controls">
        <p class="ap-layout-tip"><?php esc_html_e('Drag cards to rearrange them. Use the menu to show or hide widgets.', 'artpulse'); ?></p>
        <button id="ap-reset-layout" class="ap-form-button"><?php esc_html_e('Reset Layout', 'artpulse'); ?></button>
        <details class="ap-widget-toggle-dropdown">
        <summary><?php esc_html_e('Show/Hide Widgets', 'artpulse'); ?></summary>
        <fieldset id="ap-widget-toggles">
            <legend class="screen-reader-text"><?php esc_html_e('Toggle widgets', 'artpulse'); ?></legend>
            <?php foreach ($widgets as $wid => $_cb) : ?>
            <label><input type="checkbox" value="<?php echo esc_attr($wid); ?>" title="Toggle Widget" aria-label="Toggle Widget" <?php checked($visibility[$wid] ?? true); ?>> <?php echo esc_html(ucwords(str_replace('-', ' ', $wid))); ?></label>
            <?php endforeach; ?>
        </fieldset>
        </details>
    </div>

    <div id="ap-dashboard-widgets">
    <?php foreach ($widgets as $wid => $cb) :
        if ($wid === 'notifications' && !$show_notifications) { continue; }
        if ($wid === 'support-history' && !$show_support_history) { continue; }
        if (is_callable($cb)) {
            $visible = $visibility[$wid] ?? true;
            echo call_user_func($cb, get_defined_vars());
        }
    endforeach; ?>
    </div>
    </main>
</div>
