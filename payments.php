<?php
use ArtPulse\Payment\StripeHelper;

if (!defined('ABSPATH')) {
    exit;
}

add_action('rest_api_init', function () {
    if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/payment/intent')) {
        register_rest_route(ARTPULSE_API_NAMESPACE, '/payment/intent', [
        'methods'  => 'POST',
        'callback' => 'ap_create_payment_intent',
        'permission_callback' => function () {
            if (!current_user_can('read')) {
                return new WP_Error('rest_forbidden', __('Unauthorized.', 'artpulse'), ['status' => 403]);
            }
            return true;
        },
    ]);
    }

    if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/payment/checkout')) {
        register_rest_route(ARTPULSE_API_NAMESPACE, '/payment/checkout', [
        'methods'  => 'POST',
        'callback' => 'ap_create_checkout_session',
        'permission_callback' => function () {
            if (!current_user_can('read')) {
                return new WP_Error('rest_forbidden', __('Unauthorized.', 'artpulse'), ['status' => 403]);
            }
            return true;
        },
    ]);
    }
});

function ap_create_payment_intent(WP_REST_Request $req) {
    $amount   = intval($req->get_param('amount'));
    $currency = sanitize_text_field($req->get_param('currency') ?: 'usd');

    $intent = StripeHelper::create_intent([
        'amount'   => $amount,
        'currency' => $currency,
        'metadata' => ['user_id' => get_current_user_id()],
    ]);
    if (is_wp_error($intent)) {
        return $intent;
    }

    return rest_ensure_response(['client_secret' => $intent->client_secret]);
}

function ap_create_checkout_session(WP_REST_Request $req) {
    $price_id = sanitize_text_field($req->get_param('price_id'));
    if (!$price_id) {
        return new WP_Error('invalid_price', 'Invalid price ID', ['status' => 400]);
    }

    $session = StripeHelper::create_session([
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
    if (is_wp_error($session)) {
        return $session;
    }

    return rest_ensure_response(['id' => $session->id]);
}
