<?php
if (!defined('ABSPATH')) { exit; }

function ap_render_org_reports_page() {
    $org_id = absint(get_user_meta(get_current_user_id(), 'ap_organization_id', true));
    if (!$org_id) {
        echo '<p>' . esc_html__('No organization assigned.', 'artpulse') . '</p>';
        return;
    }

    $events = get_posts([
        'post_type'  => 'artpulse_event',
        'meta_key'   => 'ap_organization_id',
        'meta_value' => $org_id,
        'numberposts'=> 50,
    ]);
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Organization Reports', 'artpulse'); ?></h1>
        <?php if (!$events): ?>
            <p><?php esc_html_e('No events found for this organization.', 'artpulse'); ?></p>
        <?php else: ?>
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Event', 'artpulse'); ?></th>
                        <th><?php esc_html_e('Budget Export', 'artpulse'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): $base = rest_url('artpulse/v1/budget/export'); ?>
                    <tr>
                        <td><?php echo esc_html($event->post_title); ?></td>
                        <td>
                            <a class="button" href="<?php echo esc_url($base . '?event_id=' . $event->ID . '&format=pdf&_wpnonce=' . wp_create_nonce('wp_rest')); ?>">PDF</a>
                            <a class="button" href="<?php echo esc_url($base . '?event_id=' . $event->ID . '&format=csv&_wpnonce=' . wp_create_nonce('wp_rest')); ?>">CSV</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php
}

