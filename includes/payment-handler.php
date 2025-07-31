<?php
namespace ArtPulse\Payment;

use WP_Error;
use ArtPulse\Payment\StripeHelper;

class PaymentHandler
{
    public static function create_stripe_session(float $amount, array $meta = [])
    {
        $settings = get_option('artpulse_settings', []);
        $session = StripeHelper::create_session([
            'payment_method_types' => ['card'],
            'mode'                 => 'payment',
            'line_items'           => [[
                'price_data' => [
                    'currency'     => $settings['currency'] ?? 'usd',
                    'unit_amount'  => intval($amount * 100),
                    'product_data' => ['name' => 'Featured Listing'],
                ],
                'quantity' => 1,
            ]],
            'metadata'    => $meta,
            'success_url' => home_url('/?ap_payment=success'),
            'cancel_url'  => home_url('/?ap_payment=cancel'),
        ]);
        return $session;
    }
}
