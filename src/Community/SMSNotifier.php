<?php
namespace ArtPulse\Community;

class SMSNotifier {
    /**
     * Send an SMS notification if the user has opted in.
     */
    public static function maybe_send(
        int $user_id,
        string $type,
        $object_id = null,
        $related_id = null,
        string $content = ''
    ): void {
        $opt_in = get_user_meta($user_id, 'ap_sms_opt_in', true);
        $phone  = get_user_meta($user_id, 'ap_phone_number', true);

        if (!$opt_in || !$phone) {
            return;
        }

        $message = self::generate_message($type, $content);
        self::send_sms($phone, $message);
    }

    private static function send_sms(string $phone, string $message): void {
        $api_url = apply_filters('ap_sms_api_url', 'https://example.com/api/sms');
        $api_key = apply_filters('ap_sms_api_key', '');

        wp_remote_post($api_url, [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ],
            'body'    => wp_json_encode([
                'to'      => $phone,
                'message' => $message,
            ]),
            'timeout' => 15,
        ]);
    }

    private static function generate_message(string $type, string $fallback): string {
        $map = [
            'link_request_sent'    => 'New profile link request.',
            'link_request_approved'=> 'Your profile link was approved.',
            'link_request_denied'  => 'Your profile link was denied.',
            'follower'             => 'You have a new follower.',
            'favorite'             => 'Your work was favorited.',
            'favorite_added'      => 'Favorite saved.',
            'comment'              => 'New comment received.',
            'membership_upgrade'   => 'Membership upgraded.',
            'membership_downgrade' => 'Membership downgraded.',
            'membership_expired'   => 'Membership expired.',
            'payment_paid'         => 'Payment received.',
            'payment_failed'       => 'Payment failed.',
            'payment_refunded'     => 'Payment refunded.',
        ];

        return $map[$type] ?? wp_strip_all_tags($fallback);
    }
}
