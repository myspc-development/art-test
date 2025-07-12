<?php
use Stripe\StripeClient;

if (!defined('ABSPATH')) {
    exit;
}

add_action('rest_api_init', function () {
    register_rest_route('artpulse/v1', '/payment/intent', [
        'methods'  => 'POST',
        'callback' => 'ap_create_payment_intent',
        'permission_callback' => function() { return is_user_logged_in(); },
    ]);

    register_rest_route('artpulse/v1', '/payment/checkout', [
        'methods'  => 'POST',
        'callback' => 'ap_create_checkout_session',
        'permission_callback' => function() { return is_user_logged_in(); },
    ]);
});

function ap_create_payment_intent(WP_REST_Request $req) {
    $settings = get_option('artpulse_settings', []);
    $secret   = $settings['stripe_secret'] ?? '';
    if (!$secret) {
        return new WP_Error('no_secret', 'Stripe not configured', ['status' => 500]);
    }

    $amount   = intval($req->get_param('amount'));
    $currency = sanitize_text_field($req->get_param('currency') ?: 'usd');

    $stripe = new StripeClient($secret);
    try {
        $intent = $stripe->paymentIntents->create([
            'amount'   => $amount,
            'currency' => $currency,
            'metadata' => ['user_id' => get_current_user_id()],
        ]);
    } catch (Exception $e) {
        return new WP_Error('stripe_error', $e->getMessage(), ['status' => 500]);
    }

    return rest_ensure_response(['client_secret' => $intent->client_secret]);
}

function ap_create_checkout_session(WP_REST_Request $req) {
    $settings = get_option('artpulse_settings', []);
    $secret   = $settings['stripe_secret'] ?? '';
    if (!$secret) {
        return new WP_Error('no_secret', 'Stripe not configured', ['status' => 500]);
    }

    $price_id = sanitize_text_field($req->get_param('price_id'));
    if (!$price_id) {
        return new WP_Error('invalid_price', 'Invalid price ID', ['status' => 400]);
    }

    $stripe = new StripeClient($secret);
    try {
        $session = $stripe->checkout->sessions->create([
            'client_reference_id' => get_current_user_id(),
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price'    => $price_id,
                'quantity' => 1,
            ]],
            'mode'        => 'payment',
            'success_url' => home_url('/?payment=success'),
            'cancel_url'  => home_url('/?payment=cancel'),
        ]);
    } catch (Exception $e) {
        return new WP_Error('stripe_error', $e->getMessage(), ['status' => 500]);
    }

    return rest_ensure_response(['id' => $session->id]);
}
