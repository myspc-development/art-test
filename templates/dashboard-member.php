<?php
/**
 * Member dashboard template.
 */
?>
<div id="nectar-outer"><div class="container-wrap"><div class="container"><div class="row"><div class="col-md-8 col-md-offset-2"><div class="ap-dashboard">
    <a href="#main-content" class="skip-link"><?php esc_html_e('Skip to main content', 'artpulse'); ?></a>
    <nav class="dashboard-nav">
        <a href="#membership"><span class="dashicons dashicons-admin-users"></span><?php esc_html_e('Membership', 'artpulse'); ?></a>
        <a href="#upgrade"><span class="dashicons dashicons-star-filled"></span><?php esc_html_e('Upgrade', 'artpulse'); ?></a>
        <a href="#local-events"><span class="dashicons dashicons-location-alt"></span><?php esc_html_e('Local Events', 'artpulse'); ?></a>
        <a href="#favorites"><span class="dashicons dashicons-heart"></span><?php esc_html_e('Favorites', 'artpulse'); ?></a>
        <a href="#events"><span class="dashicons dashicons-calendar"></span><?php esc_html_e('Events', 'artpulse'); ?></a>
        <a href="#engagement"><span class="dashicons dashicons-format-status"></span><?php esc_html_e('Activity', 'artpulse'); ?></a>
        <a href="#account-tools"><span class="dashicons dashicons-download"></span><?php esc_html_e('Account', 'artpulse'); ?></a>
        <?php if ( $show_notifications ) : ?>
        <a href="#notifications"><span class="dashicons dashicons-megaphone"></span><?php esc_html_e('Notifications', 'artpulse'); ?></a>
        <?php endif; ?>
    </nav>

    <div id="ap-layout-controls">
        <button id="ap-reset-layout" class="ap-form-button"><?php esc_html_e('Reset Layout', 'artpulse'); ?></button>
        <fieldset id="ap-widget-toggles">
            <legend class="screen-reader-text"><?php esc_html_e('Toggle widgets', 'artpulse'); ?></legend>
            <label><input type="checkbox" value="membership" checked> <?php esc_html_e('Membership', 'artpulse'); ?></label>
            <label><input type="checkbox" value="upgrade" checked> <?php esc_html_e('Upgrade', 'artpulse'); ?></label>
            <label><input type="checkbox" value="local-events" checked> <?php esc_html_e('Local Events', 'artpulse'); ?></label>
            <label><input type="checkbox" value="favorites" checked> <?php esc_html_e('Favorites', 'artpulse'); ?></label>
            <label><input type="checkbox" value="rsvps" checked> <?php esc_html_e('RSVPs', 'artpulse'); ?></label>
            <label><input type="checkbox" value="engagement" checked> <?php esc_html_e('Activity', 'artpulse'); ?></label>
            <label><input type="checkbox" value="my-events" checked> <?php esc_html_e('My Events', 'artpulse'); ?></label>
            <label><input type="checkbox" value="events" checked> <?php esc_html_e('Events', 'artpulse'); ?></label>
            <?php if ($show_support_history) : ?>
            <label><input type="checkbox" value="support-history" checked> <?php esc_html_e('Support History', 'artpulse'); ?></label>
            <?php endif; ?>
            <?php if ($show_notifications) : ?>
            <label><input type="checkbox" value="notifications" checked> <?php esc_html_e('Notifications', 'artpulse'); ?></label>
            <?php endif; ?>
            <label><input type="checkbox" value="account-tools" checked> <?php esc_html_e('Account Tools', 'artpulse'); ?></label>
        </fieldset>
    </div>

    <div id="ap-dashboard-widgets">

    <div class="dashboard-widget" data-widget="membership">
        <h2 id="membership"><?php _e('Subscription Status','artpulse'); ?></h2>
        <div id="ap-membership-info"></div>
        <?php if ( !empty($badges) ) : ?>
        <div class="ap-badges"></div>
        <?php endif; ?>
        <div id="ap-membership-actions"></div>
        <a class="ap-edit-profile-link ap-form-button nectar-button" href="<?php echo esc_url($profile_edit_url); ?>"><?php esc_html_e('Edit Profile', 'artpulse'); ?></a>
    </div>
    <div class="dashboard-widget" data-widget="upgrade">
        <h2 id="upgrade"><?php _e('Upgrade Your Account','artpulse'); ?></h2>
        <div id="ap-upgrade-options"></div>
        <?php if ($show_forms) : ?>
        <div class="ap-dashboard-forms">
            <?php echo $artist_form; ?>
            <?php echo $org_form; ?>
        </div>
        <?php endif; ?>
    </div>
    <div class="dashboard-widget" data-widget="local-events">
        <h2 id="local-events"><?php _e('Events Near You','artpulse'); ?></h2>
        <div id="ap-local-events"></div>
    </div>
    <div class="dashboard-widget" data-widget="favorites">
        <h2 id="favorites"><?php _e('My Favorites','artpulse'); ?></h2>
        <div id="ap-favorite-events"></div>
    </div>
    <div class="dashboard-widget" data-widget="rsvps">
        <h2 id="rsvps"><?php _e('My RSVPs','artpulse'); ?></h2>
        <div id="ap-rsvp-events"></div>
    </div>
    <div class="dashboard-widget" data-widget="engagement">
        <h2 id="engagement"><?php _e('Recent Activity','artpulse'); ?></h2>
        <div id="ap-engagement-feed"></div>
        <button id="ap-engagement-load-more" class="ap-form-button"><?php esc_html_e('Load More','artpulse'); ?></button>
    </div>
    <div class="dashboard-widget" data-widget="my-events">
        <h2 id="my-events"><?php _e('My Events','artpulse'); ?></h2>
        <div id="ap-dashboard-stats" class="ap-dashboard-stats"></div>
        <div id="ap-next-event"></div>
        <div id="ap-my-events"></div>
        <canvas id="ap-trends-chart" height="150"></canvas>
        <canvas id="ap-user-engagement-chart" height="150"></canvas>
        <canvas id="ap-profile-metrics-chart" height="150"></canvas>
    </div>
    <div class="dashboard-widget" data-widget="events">
        <h2 id="events"><?php _e('Upcoming Events','artpulse'); ?></h2>
        <div id="ap-events-feed"></div>
    </div>
    <?php if ($show_support_history) : ?>
    <div class="dashboard-widget" data-widget="support-history">
        <section id="support-history">
            <h2><?php _e('Support History','artpulse'); ?></h2>
            <div id="ap-support-history"></div>
        </section>
    </div>
    <?php endif; ?>
    <?php if ($show_notifications) : ?>
    <div class="dashboard-widget" data-widget="notifications">
        <h2 id="notifications"><?php _e('Notifications','artpulse'); ?></h2>
        <div id="ap-dashboard-notifications"></div>
    </div>
    <?php endif; ?>
    <div class="dashboard-widget" data-widget="account-tools">
        <h2 id="account-tools"><?php _e('Account Tools','artpulse'); ?></h2>
        <div id="ap-account-tools">
            <button id="ap-export-json" class="ap-form-button nectar-button"><?php esc_html_e('Export JSON','artpulse'); ?></button>
            <button id="ap-export-csv" class="ap-form-button nectar-button"><?php esc_html_e('Export CSV','artpulse'); ?></button>
            <button id="ap-delete-account" class="ap-form-button nectar-button"><?php esc_html_e('Delete Account','artpulse'); ?></button>
        </div>
    </div>
    </div>
</div></div></div></div></div>
