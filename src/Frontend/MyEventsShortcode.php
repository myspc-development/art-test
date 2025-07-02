<?php

namespace ArtPulse\Frontend;

class MyEventsShortcode {

    public static function register() {
        add_shortcode('ap_my_events', [self::class, 'render']);
        add_action('init', [self::class, 'handle_deletion']);
        add_action('init', [self::class, 'handle_bulk_actions']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_styles']);
    }

    public static function enqueue_styles(): void {
        if (function_exists('ap_enqueue_global_styles')) {
            ap_enqueue_global_styles();
        }
    }

    public static function render($atts) {
        if (!is_user_logged_in()) {
            return '<p>You must be logged in to view your submitted events.</p>';
        }

        $current_user_id = get_current_user_id();

        $args = [
            'post_type'      => 'artpulse_event',
            'author'         => $current_user_id,
            'post_status'    => ['publish', 'pending', 'draft'],
            'posts_per_page' => 10,
        ];

        $events = get_posts($args);

        if (empty($events)) {
            return '<p>You haven’t submitted any events yet.</p>';
        }

        wp_enqueue_style('fullcalendar-css', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css');
        wp_enqueue_script('fullcalendar-js', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.js', [], null, true);
        wp_enqueue_script('ap-dashboard-calendar', plugin_dir_url(ARTPULSE_PLUGIN_FILE) . 'assets/js/ap-dashboard-calendar.js', ['fullcalendar-js'], '1.0', true);
        wp_localize_script('ap-dashboard-calendar', 'APArtistCalendar', [
            'rest_root' => esc_url_raw(rest_url()),
            'nonce'     => wp_create_nonce('wp_rest')
        ]);

        ob_start();
        ?>
        <div id="artist-events-calendar"></div>
        <form id="ap-bulk-event-actions" method="post">
            <?php wp_nonce_field('ap_bulk_events', 'ap_bulk_nonce'); ?>
            <select name="ap_bulk_action">
                <option value=""><?php esc_html_e('Bulk Actions', 'artpulse'); ?></option>
                <option value="close_rsvps"><?php esc_html_e('Close RSVPs', 'artpulse'); ?></option>
                <option value="duplicate"><?php esc_html_e('Duplicate', 'artpulse'); ?></option>
                <option value="delete"><?php esc_html_e('Delete', 'artpulse'); ?></option>
                <option value="export"><?php esc_html_e('Export Attendees', 'artpulse'); ?></option>
                <option value="publish"><?php esc_html_e('Publish', 'artpulse'); ?></option>
                <option value="draft"><?php esc_html_e('Mark Draft', 'artpulse'); ?></option>
            </select>
            <button type="submit">Apply</button>
            <table class="ap-my-events-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="ap-check-all" /></th>
                        <th><?php esc_html_e('Event Title', 'artpulse'); ?></th>
                        <th><?php esc_html_e('Date', 'artpulse'); ?></th>
                        <th><?php esc_html_e('Status', 'artpulse'); ?></th>
                        <th><?php esc_html_e('Actions', 'artpulse'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($events as $event):
                    $edit_url = get_edit_post_link($event->ID);
                ?>
                    <tr>
                        <td><input type="checkbox" name="event_ids[]" value="<?php echo esc_attr($event->ID); ?>" /></td>
                        <td><?php echo esc_html($event->post_title); ?></td>
                        <td><?php echo esc_html(get_post_meta($event->ID, '_ap_event_date', true)); ?></td>
                        <td><?php echo esc_html(ucfirst($event->post_status)); ?></td>
                        <td><a href="<?php echo esc_url($edit_url); ?>">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </form>
        <script>
        document.getElementById('ap-check-all').addEventListener('change', function(e){
            var boxes = document.querySelectorAll('#ap-bulk-event-actions input[name="event_ids[]"]');
            for (var i=0; i<boxes.length; i++) { boxes[i].checked = e.target.checked; }
        });
        </script>
        <?php
        return ob_get_clean();
    }

    public static function handle_deletion() {
        if (
            !is_user_logged_in() ||
            !isset($_GET['ap_delete_event']) ||
            !isset($_GET['ap_nonce'])
        ) {
            return;
        }

        $event_id = absint($_GET['ap_delete_event']);
        $nonce = sanitize_text_field($_GET['ap_nonce']);

        if (!wp_verify_nonce($nonce, 'ap_delete_event_' . $event_id)) {
            return;
        }

        $event = get_post($event_id);
        if ($event && $event->post_type === 'artpulse_event' && $event->post_author == get_current_user_id()) {
            wp_trash_post($event_id);
            wp_safe_redirect(remove_query_arg(['ap_delete_event', 'ap_nonce']));
            exit;
        }
    }

    public static function handle_bulk_actions() {
        if (
            !is_user_logged_in() ||
            !isset($_POST['ap_bulk_action']) ||
            empty($_POST['event_ids'])
        ) {
            return;
        }

        $nonce = sanitize_text_field($_POST['ap_bulk_nonce'] ?? '');
        if (!wp_verify_nonce($nonce, 'ap_bulk_events')) {
            return;
        }

        $action    = sanitize_text_field($_POST['ap_bulk_action']);
        $event_ids = array_map('intval', (array) $_POST['event_ids']);

        foreach ($event_ids as $id) {
            $event = get_post($id);
            if (!$event || $event->post_type !== 'artpulse_event' || $event->post_author != get_current_user_id()) {
                continue;
            }
            switch ($action) {
                case 'close_rsvps':
                    update_post_meta($id, 'event_rsvp_enabled', '0');
                    break;
                case 'duplicate':
                    $new_id = wp_insert_post([
                        'post_type'   => 'artpulse_event',
                        'post_status' => 'draft',
                        'post_title'  => $event->post_title,
                        'post_content'=> $event->post_content,
                        'post_author' => $event->post_author,
                    ]);
                    if (!is_wp_error($new_id)) {
                        $meta_keys = [ '_ap_event_date', 'event_start_date', 'event_end_date', 'event_recurrence_rule' ];
                        foreach ($meta_keys as $key) {
                            $val = get_post_meta($id, $key, true);
                            if ($val) {
                                update_post_meta($new_id, $key, maybe_unserialize($val));
                            }
                        }
                    }
                    break;
                case 'delete':
                    wp_trash_post($id);
                    break;
                case 'export':
                    $request = new \WP_REST_Request('GET');
                    $request->set_param('id', $id);
                    $resp = \ArtPulse\Rest\RsvpRestController::export_attendees($request);
                    header('Content-Type: text/csv');
                    header('Content-Disposition: attachment; filename="event-' . $id . '-attendees.csv"');
                    echo $resp->get_data();
                    exit;
                case 'publish':
                    wp_update_post(['ID' => $id, 'post_status' => 'publish']);
                    break;
                case 'draft':
                    wp_update_post(['ID' => $id, 'post_status' => 'draft']);
                    break;
            }
        }

        wp_safe_redirect(wp_get_referer() ?: home_url());
        exit;
    }
}
