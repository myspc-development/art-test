<?php
/**
 * Organization dashboard template.
 */
?>
<div id="nectar-outer"><div class="container-wrap"><div class="container"><div class="row"><div class="col-md-8 col-md-offset-2"><div class="ap-dashboard">
    <nav class="dashboard-nav">
        <a href="#membership"><span class="dashicons dashicons-admin-users"></span><?php esc_html_e('Membership', 'artpulse'); ?></a>
        <a href="#next-payment"><span class="dashicons dashicons-money"></span><?php esc_html_e('Next Payment', 'artpulse'); ?></a>
        <a href="#transactions"><span class="dashicons dashicons-list-view"></span><?php esc_html_e('Transactions', 'artpulse'); ?></a>
        <a href="#upgrade"><span class="dashicons dashicons-star-filled"></span><?php esc_html_e('Upgrade', 'artpulse'); ?></a>
        <a href="#content"><span class="dashicons dashicons-media-default"></span><?php esc_html_e('Content', 'artpulse'); ?></a>
        <a href="#local-events"><span class="dashicons dashicons-location-alt"></span><?php esc_html_e('Local Events', 'artpulse'); ?></a>
        <a href="#favorites"><span class="dashicons dashicons-heart"></span><?php esc_html_e('Favorites', 'artpulse'); ?></a>
        <a href="#events"><span class="dashicons dashicons-calendar"></span><?php esc_html_e('Events', 'artpulse'); ?></a>
        <a href="#account-tools"><span class="dashicons dashicons-download"></span><?php esc_html_e('Account', 'artpulse'); ?></a>
        <?php if ( $show_notifications ) : ?>
        <a href="#notifications"><span class="dashicons dashicons-megaphone"></span><?php esc_html_e('Notifications', 'artpulse'); ?></a>
        <?php endif; ?>
    </nav>

    <h2 id="membership"><?php _e('Subscription Status','artpulse'); ?></h2>
    <div id="ap-membership-info"></div>
    <?php if ( !empty($badges) ) : ?>
    <div class="ap-badges"></div>
    <?php endif; ?>
    <div id="ap-membership-actions"></div>
    <h2 id="next-payment"><?php _e('Next Payment','artpulse'); ?></h2>
    <div id="ap-next-payment"></div>
    <h2 id="transactions"><?php _e('Recent Transactions','artpulse'); ?></h2>
    <div id="ap-transactions"></div>
    <a class="ap-edit-profile-link ap-form-button nectar-button" href="<?php echo esc_url($profile_edit_url); ?>"><?php esc_html_e('Edit Profile', 'artpulse'); ?></a>
    <h2 id="upgrade"><?php _e('Upgrade Your Account','artpulse'); ?></h2>
    <div id="ap-upgrade-options"></div>
    <?php if ($show_forms) : ?>
    <div class="ap-dashboard-forms">
        <?php echo $artist_form; ?>
        <?php echo $org_form; ?>
    </div>
    <?php endif; ?>
    <h2 id="content"><?php _e('Your Content','artpulse'); ?></h2>
    <div id="ap-user-content"></div>
    <h2 id="local-events"><?php _e('Events Near You','artpulse'); ?></h2>
    <div id="ap-local-events"></div>
    <h2 id="favorites"><?php _e('My Favorites','artpulse'); ?></h2>
    <div id="ap-favorite-events"></div>
    <h2 id="rsvps"><?php _e('My RSVPs','artpulse'); ?></h2>
    <div id="ap-rsvp-events"></div>
    <h2 id="my-events"><?php _e('My Events','artpulse'); ?></h2>
    <div id="ap-dashboard-stats" class="ap-dashboard-stats"></div>
    <div id="ap-next-event"></div>
    <div id="ap-my-events"></div>
    <canvas id="ap-trends-chart" height="150"></canvas>
    <h2 id="events"><?php _e('Upcoming Events','artpulse'); ?></h2>
    <div id="ap-events-feed"></div>
    <?php if ($show_support_history) : ?>
    <section id="support-history">
        <h2><?php _e('Support History','artpulse'); ?></h2>
        <div id="ap-support-history"></div>
    </section>
    <?php endif; ?>
    <?php if ($show_notifications) : ?>
    <h2 id="notifications"><?php _e('Notifications','artpulse'); ?></h2>
    <div id="ap-dashboard-notifications"></div>
    <?php endif; ?>
    <h2 id="account-tools"><?php _e('Account Tools','artpulse'); ?></h2>
    <div id="ap-account-tools">
        <button id="ap-export-json" class="ap-form-button nectar-button"><?php esc_html_e('Export JSON','artpulse'); ?></button>
        <button id="ap-export-csv" class="ap-form-button nectar-button"><?php esc_html_e('Export CSV','artpulse'); ?></button>
        <button id="ap-delete-account" class="ap-form-button nectar-button"><?php esc_html_e('Delete Account','artpulse'); ?></button>
    </div>
</div></div></div></div></div>
