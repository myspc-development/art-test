<?php
namespace ArtPulse\Core;

class EmailService
{
    /**
     * Send an email using the configured delivery method.
     */
    public static function send($to, string $subject, string $message, array $headers = [], array $attachments = []): bool
    {
        $options       = get_option('artpulse_settings', []);
        $method        = $options['email_method'] ?? 'wp_mail';
        $from_name     = $options['email_from_name'] ?? '';
        $from_address  = $options['email_from_address'] ?? '';

        $from_cb  = null;
        $name_cb  = null;
        if ($from_address) {
            $from_cb = static function () use ($from_address) { return $from_address; };
            add_filter('wp_mail_from', $from_cb);
        }
        if ($from_name) {
            $name_cb = static function () use ($from_name) { return $from_name; };
            add_filter('wp_mail_from_name', $name_cb);
        }

        try {
            switch ($method) {
                case 'mailgun':
                    return self::send_mailgun($to, $subject, $message, $options['mailgun_api_key'] ?? '', $from_name, $from_address);
                case 'sendgrid':
                    return self::send_sendgrid($to, $subject, $message, $options['sendgrid_api_key'] ?? '', $from_name, $from_address);
                default:
                    return wp_mail($to, $subject, $message, $headers, $attachments);
            }
        } finally {
            if ($from_cb) {
                remove_filter('wp_mail_from', $from_cb);
            }
            if ($name_cb) {
                remove_filter('wp_mail_from_name', $name_cb);
            }
        }
    }

    private static function send_mailgun($to, string $subject, string $message, string $api_key, string $from_name, string $from_address): bool
    {
        if (!$api_key) {
            return false;
        }
        $from = $from_address;
        if ($from_name) {
            $from = sprintf('%s <%s>', $from_name, $from_address);
        }
        $response = wp_remote_post('https://api.mailgun.net/v3/messages', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode('api:' . $api_key),
            ],
            'body'    => [
                'from'    => $from,
                'to'      => $to,
                'subject' => $subject,
                'text'    => $message,
            ],
        ]);
        return !is_wp_error($response);
    }

    private static function send_sendgrid($to, string $subject, string $message, string $api_key, string $from_name, string $from_address): bool
    {
        if (!$api_key) {
            return false;
        }
        $payload = [
            'personalizations' => [
                [
                    'to' => [ ['email' => $to] ],
                ],
            ],
            'from' => [ 'email' => $from_address, 'name' => $from_name ],
            'subject' => $subject,
            'content' => [ [ 'type' => 'text/plain', 'value' => $message ] ],
        ];
        $response = wp_remote_post('https://api.sendgrid.com/v3/mail/send', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode($payload),
        ]);
        return !is_wp_error($response);
    }
}
