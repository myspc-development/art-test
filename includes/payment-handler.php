<?php
namespace ArtPulse\Payment;

use Stripe\StripeClient;
use WP_Error;

class PaymentHandler
{
    public static function create_stripe_session(float $amount, array $meta = [])
    {
        $settings = get_option('artpulse_settings', []);
        $secret   = $settings['stripe_secret_key'] ?? ($settings['stripe_secret'] ?? '');
        if (!$secret || !class_exists(StripeClient::class)) {
            return new WP_Error('stripe_unavailable', 'Stripe not configured.');
        }
        $stripe = new StripeClient($secret);
        try {
            $session = $stripe->checkout->sessions->create([
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
        } catch (\Exception $e) {
            return new WP_Error('stripe_error', $e->getMessage());
        }
        return $session;
    }
}
