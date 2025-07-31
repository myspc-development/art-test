<?php
namespace ArtPulse\Monetization;

use ArtPulse\Traits\Registerable;

class DonationLink
{
    use Registerable;

    private const HOOKS = [
        'rest_prepare_user' => [
            'method'   => 'add_meta',
            'type'     => 'filter',
            'priority' => 10,
            'args'     => 2,
        ],
    ];

    public static function add_meta($response, $user)
    {
        $url = get_user_meta($user->ID, 'donation_url', true);
        $response->data['donation_url'] = $url ? esc_url_raw($url) : '';
        return $response;
    }
}
