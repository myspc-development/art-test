<?php
namespace ArtPulse\Engagement;

/**
 * Handles generation and sending of digest emails to users.
 */
class DigestMailer
{
    /**
     * Register hooks.
     */
    public static function register(): void
    {
        add_action('init', [self::class, 'schedule_cron']);
        add_filter('cron_schedules', [self::class, 'add_weekly_schedule']);
        add_action('ap_send_digests', [self::class, 'send_digests']);
    }

    /**
     * Schedule the daily cron event.
     */
    public static function schedule_cron(): void
    {
        if (!wp_next_scheduled('ap_send_digests')) {
            $time = strtotime('next Sunday 7am');
            wp_schedule_event($time, 'weekly', 'ap_send_digests');
        }
    }

    public static function add_weekly_schedule(array $schedules): array
    {
        if (!isset($schedules['weekly'])) {
            $schedules['weekly'] = [
                'interval' => WEEK_IN_SECONDS,
                'display'  => __('Once Weekly', 'artpulse'),
            ];
        }
        return $schedules;
    }

    /**
     * Cron callback to send digests to users.
     */
    public static function send_digests(): void
    {
        $users = get_users([
            'meta_query' => [
                [
                    'key'     => 'ap_digest_frequency',
                    'value'   => 'none',
                    'compare' => '!=',
                ],
            ],
        ]);

        foreach ($users as $user) {
            if (self::is_digest_day($user->ID)) {
                self::send_digest($user->ID);
            }
        }
    }

    /**
     * Determine if today is the user's digest day.
     */
    public static function is_digest_day(int $user_id): bool
    {
        $frequency = get_user_meta($user_id, 'ap_digest_frequency', true);
        if ($frequency === 'weekly') {
            return (int) current_time('w') === 1; // Monday
        }
        if ($frequency === 'monthly') {
            return (int) current_time('j') === 1; // First day of month
        }
        return false;
    }

    /**
     * Send digest email to a user.
     */
    public static function send_digest(int $user_id): void
    {
        $user = get_userdata($user_id);
        if (!$user) {
            return;
        }

        [$html, $plain] = self::generate_digest($user_id);

        $subject = __('Your ArtPulse Digest', 'artpulse');
        $content = apply_filters('artpulse_digest_content', $html, $user_id);
        $headers = [ 'Content-Type: text/html; charset=UTF-8' ];

        // Basic unsubscribe footer
        $content .= sprintf("<p><a href='%s'>%s</a></p>",
            site_url('?ap_unsubscribe=1&user=' . $user_id),
            __('Unsubscribe', 'artpulse')
        );

        \ArtPulse\Core\EmailService::send(
            $user->user_email,
            $subject,
            $content,
            $headers
        );
    }

    /**
     * Generate digest content for a user.
     *
     * @return array Array containing HTML and plain text.
     */
    public static function generate_digest(int $user_id): array
    {
        $today  = current_time('Y-m-d');

        $events = [];
        $seen   = [];

        $add_event = static function (int $event_id) use (&$events, &$seen, $today): void {
            if (isset($seen[$event_id])) {
                return;
            }
            $date = get_post_meta($event_id, 'event_start_date', true);
            if (!$date || strtotime($date) < strtotime($today)) {
                return;
            }
            $events[] = [
                'id'       => $event_id,
                'title'    => get_the_title($event_id),
                'link'     => get_permalink($event_id),
                'date'     => $date,
                'location' => get_post_meta($event_id, '_ap_event_location', true),
            ];
            $seen[$event_id] = true;
        };

        // --- Gather events from follows ---
        $follows = get_user_meta($user_id, '_ap_follows', true);
        if (is_array($follows)) {
            foreach ($follows as $fid) {
                $post_type = get_post_type($fid);
                if (!$post_type) {
                    continue;
                }
                if ($post_type === 'artpulse_event') {
                    $add_event((int) $fid);
                } elseif ($post_type === 'artpulse_artist') {
                    $posts = get_posts([
                        'post_type'      => 'artpulse_event',
                        'post_status'    => 'publish',
                        'numberposts'    => -1,
                        'meta_query'     => [
                            [
                                'key'     => '_ap_event_artists',
                                'value'   => 'i:' . (int) $fid . ';',
                                'compare' => 'LIKE',
                            ],
                            [
                                'key'     => 'event_start_date',
                                'value'   => $today,
                                'type'    => 'DATE',
                                'compare' => '>=',
                            ],
                        ],
                    ]);
                    foreach ($posts as $p) {
                        $add_event($p->ID);
                    }
                } elseif ($post_type === 'artpulse_org') {
                    $posts = get_posts([
                        'post_type'      => 'artpulse_event',
                        'post_status'    => 'publish',
                        'numberposts'    => -1,
                        'meta_query'     => [
                            'relation' => 'AND',
                            [
                                'relation' => 'OR',
                                [
                                    'key'   => '_ap_event_organization',
                                    'value' => (int) $fid,
                                ],
                                [
                                    'key'     => '_ap_event_organizations',
                                    'value'   => 'i:' . (int) $fid . ';',
                                    'compare' => 'LIKE',
                                ],
                            ],
                            [
                                'key'     => 'event_start_date',
                                'value'   => $today,
                                'type'    => 'DATE',
                                'compare' => '>=',
                            ],
                        ],
                    ]);
                    foreach ($posts as $p) {
                        $add_event($p->ID);
                    }
                }
            }
        }

        // --- Events near the user's location ---
        $city  = get_user_meta($user_id, 'ap_city', true);
        $state = get_user_meta($user_id, 'ap_state', true);
        if ($city || $state) {
            $meta_query = [
                [
                    'key'     => 'event_start_date',
                    'value'   => $today,
                    'type'    => 'DATE',
                    'compare' => '>=',
                ],
            ];
            if ($city) {
                $meta_query[] = [
                    'key'   => 'event_city',
                    'value' => $city,
                ];
            }
            if ($state) {
                $meta_query[] = [
                    'key'   => 'event_state',
                    'value' => $state,
                ];
            }

            $posts = get_posts([
                'post_type'      => 'artpulse_event',
                'post_status'    => 'publish',
                'numberposts'    => 5,
                'meta_query'     => $meta_query,
            ]);
            foreach ($posts as $p) {
                $add_event($p->ID);
            }
        }

        // Sort events by date ascending
        usort($events, static function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        $html  = '<h1>' . esc_html__('ArtPulse Digest', 'artpulse') . '</h1>';
        $plain = "ArtPulse Digest\n";

        if ($events) {
            $html  .= '<p>' . esc_html__('Here are upcoming events you may be interested in:', 'artpulse') . '</p><ul>';
            $plain .= "\n" . __('Here are upcoming events you may be interested in:', 'artpulse') . "\n";
            foreach ($events as $e) {
                $date_str = $e['date'] ? date_i18n('M d, Y', strtotime($e['date'])) : '';
                $html .= sprintf(
                    '<li><a href="%s">%s</a> - %s%s</li>',
                    esc_url($e['link']),
                    esc_html($e['title']),
                    esc_html($date_str),
                    $e['location'] ? ' – ' . esc_html($e['location']) : ''
                );
                $plain .= sprintf(
                    "- %s - %s\n  %s\n",
                    $e['title'],
                    $date_str,
                    $e['link']
                );
            }
            $html .= '</ul>';
        } else {
            $msg  = __('No upcoming events found.', 'artpulse');
            $html .= '<p>' . esc_html($msg) . '</p>';
            $plain .= "\n" . $msg;
        }

        return [$html, $plain];
    }
}
