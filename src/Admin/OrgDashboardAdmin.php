<?php
namespace ArtPulse\Admin;

use Stripe\StripeClient;
use Dompdf\Dompdf;
use ArtPulse\Admin\OrgCommunicationsCenter;

class OrgDashboardAdmin {
    public static function register() {
        add_menu_page(
            'Organization Dashboard',
            'Org Dashboard',
            'view_artpulse_dashboard',
            'ap-org-dashboard',
            [self::class, 'render'],
            'dashicons-building'
        );
        add_action('admin_init', [self::class, 'handleExports']);
    }

    // Hide the default Organizations CPT menu for non-admins
    public static function hide_org_menu() {
        if (!current_user_can('manage_options')) {
            remove_menu_page('edit.php?post_type=artpulse_org');
        }
    }

    /**
     * Handle CSV or PDF billing exports before page output.
     */
    public static function handleExports(): void
    {
        if (($_GET['page'] ?? '') !== 'ap-org-dashboard') {
            return;
        }

        if (isset($_GET['ap_export_billing_csv'])) {
            self::exportBillingCsv();
        } elseif (isset($_GET['ap_export_billing_pdf'])) {
            self::exportBillingPdf();
        }
    }

    // --- Helper: Get the current org id for this admin ---
    private static function get_current_org_id() {
        // Super admins can choose any org via dropdown (?org_id=)
        if (current_user_can('administrator')) {
            if (isset($_GET['org_id'])) {
                return intval($_GET['org_id']);
            }
            // Optionally default to first org
            $orgs = get_posts([
                'post_type' => 'artpulse_org',
                'numberposts' => 1,
                'post_status' => 'publish',
            ]);
            return $orgs ? $orgs[0]->ID : 0;
        }
        // For org admins, load from user meta (example key: ap_organization_id)
        $org_id = intval(get_user_meta(get_current_user_id(), 'ap_organization_id', true));
        return $org_id;
    }

    /**
     * Retrieve all organization posts with caching.
     *
     * @return array<int, \WP_Post>
     */
    private static function get_all_orgs(): array
    {
        $key = 'ap_dash_all_orgs';
        $orgs = get_transient($key);
        if ($orgs === false) {
            $orgs = get_posts([
                'post_type'   => 'artpulse_org',
                'numberposts' => -1,
                'post_status' => 'publish',
            ]);
            set_transient($key, $orgs, MINUTE_IN_SECONDS * 15);
        }
        return $orgs;
    }

    /**
     * Retrieve posts for an organization with caching.
     *
     * @param int   $org_id Organization ID
     * @param string $suffix Cache key suffix
     * @param array  $args   WP_Query arguments
     * @return array<int, \WP_Post>
     */
    private static function get_org_posts(int $org_id, string $suffix, array $args): array
    {
        $key = 'ap_dash_' . $suffix . '_' . $org_id;
        $posts = get_transient($key);
        if ($posts === false) {
            $posts = get_posts($args);
            set_transient($key, $posts, MINUTE_IN_SECONDS * 15);
        }
        return $posts;
    }
    
    public static function render() {
        echo '<div class="wrap"><h1>Organization Dashboard</h1>';

        // Show org select dropdown for super admins only
        if (current_user_can('administrator')) {
            $all_orgs = self::get_all_orgs();
            $selected_org = self::get_current_org_id();
            echo '<form method="get" style="margin-bottom:1em;"><input type="hidden" name="page" value="ap-org-dashboard" />';
            echo '<label for="ap-org-select"><strong>Select Organization: </strong></label>';
            echo '<select name="org_id" id="ap-org-select" onchange="this.form.submit()">';
            foreach ($all_orgs as $org) {
                $selected = ($org->ID == $selected_org) ? 'selected' : '';
                echo '<option value="' . esc_attr($org->ID) . '" ' . $selected . '>' . esc_html(get_the_title($org)) . '</option>';
            }
            echo '</select></form>';
        }

        self::render_linked_artists();
        self::render_org_artworks();
        self::render_org_events();
        self::render_org_communications();
        self::render_org_analytics();
        self::render_billing_history();
        echo '</div>';
    }


    // --- SECTION: Linked Artists ---
    private static function render_linked_artists() {
        echo '<h2>Linked Artists</h2>';
        $org_id = self::get_current_org_id();
        if (!$org_id) {
            echo '<p>No organization assigned to your user.</p>';
            return;
        }
        $args = [
            'post_type' => 'ap_profile_link',
            'meta_query' => [
                [ 'key' => 'org_id', 'value' => $org_id ],
                [ 'key' => 'status', 'value' => 'approved' ]
            ],
            'post_status' => 'publish',
            'numberposts' => 50
        ];
        $requests = self::get_org_posts($org_id, 'profile_links', $args);
        echo '<table class="widefat"><thead><tr><th>Artist ID</th><th>Requested On</th></tr></thead><tbody>';
        foreach ($requests as $req) {
            $artist_user_id = get_post_meta($req->ID, 'artist_user_id', true);
            $requested_on = get_post_meta($req->ID, 'requested_on', true);
            echo '<tr><td>' . esc_html($artist_user_id) . '</td><td>' . esc_html($requested_on) . '</td></tr>';
        }
        echo '</tbody></table>';
    }

    /**
     * Retrieve billing rows for the current organization.
     *
     * @param int $org_id Organization post ID.
     * @return array<int, array<string, string>>
     */
    private static function getBillingRows(int $org_id): array
    {
        $payments = get_post_meta($org_id, 'stripe_payment_ids', true);
        if (empty($payments) || !is_array($payments)) {
            return [];
        }

        $settings = get_option('artpulse_settings', []);
        $secret   = $settings['stripe_secret'] ?? '';
        $stripe   = $secret ? new StripeClient($secret) : null;

        $rows = [];
        foreach ($payments as $charge_id) {
            $row = [
                'id'     => (string) $charge_id,
                'date'   => '-',
                'amount' => '-',
                'status' => '-',
            ];

            if ($stripe) {
                try {
                    $charge         = $stripe->charges->retrieve($charge_id, []);
                    $row['date']    = date_i18n(get_option('date_format'), intval($charge->created));
                    $row['amount']  = number_format_i18n($charge->amount / 100, 2) . ' ' . strtoupper($charge->currency);
                    $row['status']  = (string) $charge->status;
                } catch (\Exception $e) {
                    // Ignore failed API lookups
                }
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Output billing history as CSV file.
     */
    private static function exportBillingCsv(): void
    {
        $org_id = self::get_current_org_id();
        if (!$org_id) {
            exit;
        }

        $rows = self::getBillingRows($org_id);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="billing-' . $org_id . '.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Charge ID', 'Date', 'Amount', 'Status']);
        foreach ($rows as $row) {
            fputcsv($output, [$row['id'], $row['date'], $row['amount'], $row['status']]);
        }
        fclose($output);
        exit;
    }

    /**
     * Output billing history as a PDF file.
     */
    private static function exportBillingPdf(): void
    {
        $org_id = self::get_current_org_id();
        if (!$org_id) {
            exit;
        }

        $rows = self::getBillingRows($org_id);
        $html  = '<h1>Billing History</h1><table border="1" cellspacing="0" cellpadding="4"><thead><tr>';
        $html .= '<th>Charge ID</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead><tbody>';
        foreach ($rows as $row) {
            $html .= '<tr><td>' . esc_html($row['id']) . '</td><td>' . esc_html($row['date']) . '</td><td>' . esc_html($row['amount']) . '</td><td>' . esc_html($row['status']) . '</td></tr>';
        }
        $html .= '</tbody></table>';

        if (class_exists(Dompdf::class)) {
            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4');
            $dompdf->render();
            $dompdf->stream('billing-' . $org_id . '.pdf', ['Attachment' => true]);
        } else {
            header('Content-Type: text/html');
            echo $html;
        }
        exit;
    }

    // --- SECTION: Org Artworks ---
    private static function render_org_artworks() {
        echo '<h2>Artworks</h2>';
        $org_id = self::get_current_org_id();
        if (!$org_id) {
            echo '<p>No organization assigned to your user.</p>';
            return;
        }
        $args = [
            'post_type' => 'artpulse_artwork',
            'meta_query' => [
                [ 'key' => 'org_id', 'value' => $org_id ]
            ],
            'post_status' => 'publish',
            'numberposts' => 50
        ];
        $artworks = self::get_org_posts($org_id, 'artworks', $args);
        echo '<table class="widefat"><thead><tr><th>Artwork ID</th><th>Title</th></tr></thead><tbody>';
        foreach ($artworks as $artwork) {
            echo '<tr><td>' . $artwork->ID . '</td><td>' . esc_html(get_the_title($artwork)) . '</td></tr>';
        }
        echo '</tbody></table>';
    }

    // --- SECTION: Org Events ---
    private static function render_org_events() {
        echo '<h2>Events</h2>';
        $org_id = self::get_current_org_id();
        if (!$org_id) {
            echo '<p>No organization assigned to your user.</p>';
            return;
        }
        $args = [
            'post_type' => 'artpulse_event',
            'meta_query' => [
                [ 'key' => 'org_id', 'value' => $org_id ]
            ],
            'post_status' => 'publish',
            'numberposts' => 50
        ];
        $events = self::get_org_posts($org_id, 'events', $args);
        echo '<table class="widefat"><thead><tr><th>Event ID</th><th>Title</th></tr></thead><tbody>';
        foreach ($events as $event) {
            echo '<tr><td>' . $event->ID . '</td><td>' . esc_html(get_the_title($event)) . '</td></tr>';
        }
        echo '</tbody></table>';
    }

    // --- SECTION: Communications ---
    private static function render_org_communications(): void
    {
        echo '<h2>Communications</h2>';
        $org_id = self::get_current_org_id();
        if (!$org_id) {
            echo '<p>No organization assigned to your user.</p>';
            return;
        }
        $messages = OrgCommunicationsCenter::get_messages_for_org($org_id);
        echo '<div class="org-communications-center">';
        echo '<h3>Inbox</h3>';
        echo '<ul class="message-list">';
        foreach ($messages as $msg) {
            $unread = ($msg["status"] ?? '') === 'unread' ? 'unread' : '';
            echo '<li class="message-summary ' . $unread . '">';
            echo '<span class="msg-sender">' . esc_html($msg['user_from'] ?? '') . '</span>';
            echo '<span class="msg-subject">' . esc_html($msg['subject'] ?? '') . '</span>';
            echo '<span class="msg-event">' . esc_html($msg['event_id'] ?? '') . '</span>';
            echo '<span class="msg-date">' . esc_html($msg['created_at'] ?? '') . '</span>';
            echo '</li>';
        }
        if (empty($messages)) {
            echo '<li>' . esc_html__('No messages found.', 'artpulse') . '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }

    // --- SECTION: Org Analytics (stub) ---
    private static function render_org_analytics() {
        echo '<h2>Analytics</h2>';
        // Example analytics: total views and favorites from artworks
        $org_id = self::get_current_org_id();
        if (!$org_id) {
            echo '<p>No organization assigned to your user.</p>';
            return;
        }
        $args = [
            'post_type' => 'artpulse_artwork',
            'meta_query' => [
                [ 'key' => 'org_id', 'value' => $org_id ]
            ],
            'post_status' => 'publish',
            'numberposts' => 50
        ];
        $artworks = self::get_org_posts($org_id, 'stats_artworks', $args);
        $total_views = 0;
        $total_favorites = 0;
        foreach ($artworks as $artwork) {
            $views = intval(get_post_meta($artwork->ID, 'ap_views', true));
            $favs = intval(get_post_meta($artwork->ID, 'ap_favorites', true));
            $total_views += $views;
            $total_favorites += $favs;
        }
        echo '<p>Total Artwork Views: <strong>' . esc_html($total_views) . '</strong></p>';
        echo '<p>Total Artwork Favorites: <strong>' . esc_html($total_favorites) . '</strong></p>';
    }

    // --- SECTION: Billing History ---
    private static function render_billing_history() {
        echo '<h2>Billing History</h2>';
        $org_id = self::get_current_org_id();
        if (!$org_id) {
            echo '<p>No organization assigned to your user.</p>';
            return;
        }
        // Example: Payments stored in org meta as array of Stripe charge IDs
        $payments = get_post_meta($org_id, 'stripe_payment_ids', true);
        if (empty($payments) || !is_array($payments)) {
            echo '<p>No billing history found.</p>';
            return;
        }
        $csv_url = esc_url(add_query_arg('ap_export_billing_csv', 1));
        $pdf_url = esc_url(add_query_arg('ap_export_billing_pdf', 1));
        echo '<p><a href="' . $csv_url . '" class="button button-secondary">' . esc_html__('Export CSV', 'artpulse') . '</a> ';
        echo '<a href="' . $pdf_url . '" class="button button-secondary">' . esc_html__('Export PDF', 'artpulse') . '</a></p>';
        echo '<table class="widefat"><thead><tr><th>Charge ID</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead><tbody>';

        $settings = get_option('artpulse_settings', []);
        $secret   = $settings['stripe_secret'] ?? '';
        $stripe   = $secret ? new StripeClient($secret) : null;

        foreach ($payments as $charge_id) {
            $id = esc_html($charge_id);
            $date = $amount = $status = '-';

            if ($stripe) {
                try {
                    $charge = $stripe->charges->retrieve($charge_id, []);
                    $date   = date_i18n(get_option('date_format'), intval($charge->created));
                    $amount = number_format_i18n($charge->amount / 100, 2) . ' ' . strtoupper($charge->currency);
                    $status = esc_html($charge->status);
                } catch (\Exception $e) {
                    // Fallback to ID only on API errors
                }
            }

            echo '<tr><td>' . $id . '</td><td>' . $date . '</td><td>' . $amount . '</td><td>' . $status . '</td></tr>';
        }
        echo '</tbody></table>';
    }

    /**
     * Clear cached dashboard queries when related posts are saved.
     */
    public static function clear_cache(int $post_id, \WP_Post $post, bool $update): void
    {
        if (wp_is_post_revision($post_id)) {
            return;
        }

        if ($post->post_type === 'artpulse_org') {
            delete_transient('ap_dash_all_orgs');
            return;
        }

        if (in_array($post->post_type, ['ap_profile_link', 'artpulse_artwork', 'artpulse_event'], true)) {
            $org_id = intval(get_post_meta($post_id, 'org_id', true));
            if ($org_id) {
                delete_transient('ap_dash_profile_links_' . $org_id);
                delete_transient('ap_dash_artworks_' . $org_id);
                delete_transient('ap_dash_events_' . $org_id);
                delete_transient('ap_dash_stats_artworks_' . $org_id);
                delete_transient('ap_org_metrics_' . $org_id);
            }
        }
    }
}

add_action('admin_menu', ['\\ArtPulse\\Admin\\OrgDashboardAdmin', 'register']);
add_action('admin_menu', ['\\ArtPulse\\Admin\\OrgDashboardAdmin', 'hide_org_menu'], 999);
add_action('save_post', ['\\ArtPulse\\Admin\\OrgDashboardAdmin', 'clear_cache'], 10, 3);
