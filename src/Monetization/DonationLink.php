<?php
namespace ArtPulse\Monetization;

class DonationLink
{
    public static function register(): void
    {
        add_filter('rest_prepare_user', [self::class, 'add_meta'], 10, 2);
    }

    public static function add_meta($response, $user)
    {
        $url = get_user_meta($user->ID, 'donation_url', true);
        $response->data['donation_url'] = $url ? esc_url_raw($url) : '';
        return $response;
    }
}
