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
        add_action('ap_daily_digest', [self::class, 'send_digests']);
    }

    /**
     * Schedule the daily cron event.
     */
    public static function schedule_cron(): void
    {
        if (!wp_next_scheduled('ap_daily_digest')) {
            wp_schedule_event(strtotime('midnight'), 'daily', 'ap_daily_digest');
        }
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

        wp_mail($user->user_email, $subject, $content, $headers);
    }

    /**
     * Generate digest content for a user.
     *
     * @return array Array containing HTML and plain text.
     */
    public static function generate_digest(int $user_id): array
    {
        // Placeholder implementation. Production code would gather events
        // and recommendations based on follows and location.
        $html  = '<h1>' . esc_html__('ArtPulse Digest', 'artpulse') . '</h1>';
        $html .= '<p>' . esc_html__('Stay tuned for upcoming events!', 'artpulse') . '</p>';

        $plain = "ArtPulse Digest\n\nStay tuned for upcoming events!";

        return [ $html, $plain ];
    }
}
