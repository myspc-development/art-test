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
            <li><a href="#account-tools"><span class="dashicons dashicons-download"></span><?php esc_html_e('Account', 'artpulse'); ?></a></li>
            <?php if ( $show_notifications ) : ?>
            <li><a href="#notifications"><span class="dashicons dashicons-megaphone"></span><?php esc_html_e('Notifications', 'artpulse'); ?></a></li>
            <?php endif; ?>
        </ul>
    </aside>
    <main id="main-content">
    <div class="dashboard-main">

    <div id="ap-layout-controls">
        <p class="ap-layout-tip"><?php esc_html_e('Drag cards to rearrange them. Use the menu to show or hide widgets.', 'artpulse'); ?></p>
        <button id="ap-reset-layout" class="ap-form-button"><?php esc_html_e('Reset Layout', 'artpulse'); ?></button>
        <details class="ap-widget-toggle-dropdown">
        <summary><?php esc_html_e('Show/Hide Widgets', 'artpulse'); ?></summary>
        <fieldset id="ap-widget-toggles">
            <legend class="screen-reader-text"><?php esc_html_e('Toggle widgets', 'artpulse'); ?></legend>
            <label><input type="checkbox" value="membership" checked> <?php esc_html_e('Membership', 'artpulse'); ?></label>
            <label><input type="checkbox" value="upgrade" checked> <?php esc_html_e('Upgrade', 'artpulse'); ?></label>
            <label><input type="checkbox" value="local-events" checked> <?php esc_html_e('Local Events', 'artpulse'); ?></label>
            <label><input type="checkbox" value="favorites" checked> <?php esc_html_e('Favorites', 'artpulse'); ?></label>
            <label><input type="checkbox" value="rsvps" checked> <?php esc_html_e('RSVPs', 'artpulse'); ?></label>
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
        </details>
    </div>

    <div id="ap-dashboard-widgets">

    <div class="dashboard-card" data-widget="membership">
        <h2 id="membership"><?php _e('Subscription Status','artpulse'); ?></h2>
        <div id="ap-membership-info"></div>
        <?php if ( !empty($badges) ) : ?>
        <div class="ap-badges"></div>
        <?php endif; ?>
        <div id="ap-membership-actions"></div>
        <a class="ap-edit-profile-link ap-form-button nectar-button" href="<?php echo esc_url($profile_edit_url); ?>"><?php esc_html_e('Edit Profile', 'artpulse'); ?></a>
    </div>
    <div class="dashboard-card" data-widget="upgrade">
        <h2 id="upgrade"><?php _e('Upgrade Your Account','artpulse'); ?></h2>
        <div id="ap-upgrade-options"></div>
        <?php if ($show_forms) : ?>
        <div class="ap-dashboard-forms">
            <?php echo $artist_form; ?>
            <?php echo $org_form; ?>
        </div>
        <?php endif; ?>
    </div>
    <div class="dashboard-card" data-widget="local-events">
        <h2 id="local-events"><?php _e('Events Near You','artpulse'); ?></h2>
        <div id="ap-local-events"></div>
    </div>
    <div class="dashboard-card" data-widget="favorites">
        <h2 id="favorites"><?php _e('My Favorites','artpulse'); ?></h2>
        <?php
        use ArtPulse\Community\FavoritesManager;

        $user_id   = get_current_user_id();
        $favorites = FavoritesManager::get_favorites($user_id);

        if (empty($favorites)) {
            echo '<p>' . esc_html__('You haven’t favorited any content yet.', 'artpulse') . '</p>';
        } else {
            foreach (['event', 'artist', 'organization', 'artwork'] as $type) {
                if (!empty($favorites[$type])) {
                    echo '<div class="favorite-group favorite-group-' . esc_attr($type) . '">';
                    echo '<h3>' . esc_html(ucfirst($type)) . 's</h3>';
                    echo '<ul class="favorites-list">';
                    foreach ($favorites[$type] as $post_id) {
                        $title = get_the_title($post_id);
                        $link  = get_permalink($post_id);
                        echo '<li><a href="' . esc_url($link) . '">' . esc_html($title) . '</a></li>';
                    }
                    echo '</ul></div>';
                }
            }
        }
        ?>
    </div>
    <div class="dashboard-card" data-widget="rsvps">
        <h2 id="rsvps"><?php _e('My RSVPs','artpulse'); ?></h2>
        <div id="ap-rsvp-events"></div>
    </div>
    <div class="dashboard-card" data-widget="my-events">
        <h2 id="my-events"><?php _e('My Events','artpulse'); ?></h2>
        <div id="ap-dashboard-stats" class="ap-dashboard-stats"></div>
        <div id="ap-next-event"></div>
        <div id="ap-my-events"></div>
        <canvas id="ap-trends-chart" height="150"></canvas>
        <canvas id="ap-user-engagement-chart" height="150"></canvas>
        <canvas id="ap-profile-metrics-chart" height="150"></canvas>
        <canvas id="ap-event-analytics-chart" height="150"></canvas>
    </div>
    <div class="dashboard-card" data-widget="events">
        <h2 id="events"><?php _e('Upcoming Events','artpulse'); ?></h2>
        <div id="ap-events-feed"></div>
    </div>
    <?php if ($show_support_history) : ?>
    <div class="dashboard-card" data-widget="support-history">
        <section id="support-history">
            <h2><?php _e('Support History','artpulse'); ?></h2>
            <div id="ap-support-history"></div>
        </section>
    </div>
    <?php endif; ?>
    <?php if ($show_notifications) : ?>
    <div class="dashboard-card" data-widget="notifications">
        <h2 id="notifications"><?php _e('Notifications','artpulse'); ?></h2>
        <div id="ap-dashboard-notifications"></div>
    </div>
    <?php endif; ?>
    <div class="dashboard-card" data-widget="account-tools">
        <h2 id="account-tools"><?php _e('Account Tools','artpulse'); ?></h2>
        <div id="ap-account-tools">
            <button id="ap-export-json" class="ap-form-button nectar-button"><?php esc_html_e('Export JSON','artpulse'); ?></button>
            <button id="ap-export-csv" class="ap-form-button nectar-button"><?php esc_html_e('Export CSV','artpulse'); ?></button>
            <button id="ap-delete-account" class="ap-form-button nectar-button"><?php esc_html_e('Delete Account','artpulse'); ?></button>
        </div>
    </div>
    </div>
    </main>
</div>
