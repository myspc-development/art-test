<?php
namespace ArtPulse\Core;

use Stripe\StripeClient;
use WP_REST_Request;
use WP_Error;

class MembershipManager
{
    /**
     * Hook all actions.
     */
    public static function register()
    {
        // Assign free membership on user registration
        add_action('user_register', [ self::class, 'assignFreeMembership' ]);
        // Log registration details
        add_action('user_register', [ self::class, 'logRegistration' ]);

        // Register Stripe webhook endpoint
        add_action('rest_api_init', [ self::class, 'registerRestRoutes' ]);

        // Schedule daily expiry checks and notifications
        add_action('ap_daily_expiry_check', [ self::class, 'processExpirations' ]);
    }

    /**
     * Give every new user the Free level.
     */
    public static function assignFreeMembership($user_id)
    {
        $user = get_userdata($user_id);
        if (in_array('administrator', (array) $user->roles, true)) {
            // Don't override admin privileges when registering
            $user->add_role('member');
        } else {
            $user->set_role('member');
        }
        update_user_meta($user_id, 'ap_membership_level', 'Free');

        // Optional: send welcome email
        wp_mail(
            $user->user_email,
            __('Welcome to ArtPulse – Free Membership','artpulse'),
            __('Thanks for joining! You now have Free membership.','artpulse')
        );
    }

    /**
     * Log registration time and IP address.
     */
    public static function logRegistration($user_id)
    {
        update_user_meta($user_id, 'registered_at', current_time('mysql'));

        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if (!empty($ip)) {
            update_user_meta($user_id, 'registered_ip', sanitize_text_field($ip));
        }
    }

    /**
     * Expose a public REST endpoint for Stripe webhooks.
     */
    public static function registerRestRoutes()
    {
        register_rest_route('artpulse/v1', '/stripe-webhook', [
            'methods'             => 'POST',
            'callback'            => [ self::class, 'handleStripeWebhook' ],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * Handle incoming Stripe webhook events.
     */
    public static function handleStripeWebhook(WP_REST_Request $request)
    {
        $payload    = $request->get_body();
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $settings   = get_option('artpulse_settings', []);
        $secret     = $settings['stripe_webhook_secret'] ?? '';

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $secret);
        } catch (\UnexpectedValueException $e) {
            return new WP_Error('invalid_payload', 'Invalid payload', ['status' => 400]);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return new WP_Error('invalid_signature', 'Invalid signature', ['status' => 400]);
        }

        // Stripe client for retrieving full objects if needed
        $stripe = new StripeClient($settings['stripe_secret'] ?? '');

        switch ($event->type) {

            // Initial checkout session → customer created
            case 'checkout.session.completed':
                $session = $event->data->object;
                $user_id = absint($session->client_reference_id ?? 0);
                if ($user_id) {
                    // record customer ID and set Pro for one month
                    update_user_meta($user_id, 'stripe_customer_id', sanitize_text_field($session->customer));
                    update_user_meta($user_id, 'ap_membership_level', 'Pro');
                    $expiry = strtotime('+1 month', current_time('timestamp'));
                    update_user_meta($user_id, 'ap_membership_expires', $expiry);

                    $amount   = isset($session->amount_total) ? $session->amount_total / 100 : 0;
                    $currency = strtoupper($session->currency ?? '');
                    $renewal  = date_i18n(get_option('date_format'), $expiry);
                    $content  = sprintf(
                        'Payment received: %s %s. Next renewal on %s.',
                        number_format_i18n($amount, 2),
                        $currency,
                        $renewal
                    );
                    if (class_exists('ArtPulse\\Community\\NotificationManager')) {
                        \ArtPulse\Community\NotificationManager::add(
                            $user_id,
                            'payment_paid',
                            null,
                            null,
                            $content
                        );
                    }
                }
                break;

            // Subscription created or renewed
            case 'customer.subscription.created':
            case 'invoice.payment_succeeded':
                $sub = $event->data->object;
                $custId = $sub->customer;
                // find user by stripe_customer_id
                $user = get_users([
                    'meta_key'   => 'stripe_customer_id',
                    'meta_value' => $custId,
                    'number'     => 1,
                    'fields'     => 'ID',
                ]);
                if (!empty($user)) {
                    $user_id = $user[0];
                    update_user_meta($user_id, 'ap_membership_level', 'Pro');
                    // Stripe sends current_period_end timestamp
                    $expiry = intval($sub->current_period_end);
                    update_user_meta($user_id, 'ap_membership_expires', $expiry);

                    $amount   = isset($sub->amount_paid) ? $sub->amount_paid / 100 : 0;
                    $currency = strtoupper($sub->currency ?? '');
                    $renewal  = date_i18n(get_option('date_format'), $expiry);
                    $content  = sprintf(
                        'Payment received: %s %s. Next renewal on %s.',
                        number_format_i18n($amount, 2),
                        $currency,
                        $renewal
                    );
                    if (class_exists('ArtPulse\\Community\\NotificationManager')) {
                        \ArtPulse\Community\NotificationManager::add(
                            $user_id,
                            'payment_paid',
                            null,
                            null,
                            $content
                        );
                    }
                }
                break;

            // Subscription cancelled or payment failed → downgrade immediately
            case 'customer.subscription.deleted':
            case 'invoice.payment_failed':
                $obj = $event->data->object;
                $custId = $obj->customer;
                $user = get_users([
                    'meta_key'   => 'stripe_customer_id',
                    'meta_value' => $custId,
                    'number'     => 1,
                    'fields'     => 'ID',
                ]);
                if (!empty($user)) {
                    $user_id = $user[0];
                    // downgrade
                    $usr = get_userdata($user_id);
                    if (in_array('administrator', (array) $usr->roles, true)) {
                        // Administrators keep admin capabilities during downgrades
                        $usr->add_role('subscriber');
                    } else {
                        $usr->set_role('subscriber');
                    }
                    update_user_meta($user_id, 'ap_membership_level', 'Free');
                    update_user_meta($user_id, 'ap_membership_expires', current_time('timestamp'));

                    // notify user
                    wp_mail(
                        $usr->user_email,
                        __('Your ArtPulse membership has been cancelled','artpulse'),
                        __('Your subscription has ended or payment failed. You are now on Free membership.','artpulse')
                    );

                    $amount   = isset($obj->amount_due) ? $obj->amount_due / 100 : 0;
                    $currency = strtoupper($obj->currency ?? '');
                    $content  = $amount ?
                        sprintf(
                            'Payment of %s %s failed and your membership was downgraded.',
                            number_format_i18n($amount, 2),
                            $currency
                        ) :
                        'Payment failed and your membership was downgraded.';

                    if (class_exists('ArtPulse\\Community\\NotificationManager')) {
                        \ArtPulse\Community\NotificationManager::add(
                            $user_id,
                            'payment_failed',
                            null,
                            null,
                            $content
                        );
                        if ($event->type === 'customer.subscription.deleted') {
                            \ArtPulse\Community\NotificationManager::add(
                                $user_id,
                                'membership_expired',
                                null,
                                null,
                                'Your membership has expired and you have been moved to Free level.'
                            );
                        }
                    }
                }
                break;

            // Add other event types here…

            default:
                // do nothing
                break;
        }

        return rest_ensure_response(['received' => true]);
    }

    /**
     * Demote any users whose membership has expired.
     * Runs daily via cron.
     */
    public static function processExpirations()
    {
        $now = current_time('timestamp');
        $expired = get_users([
            'meta_key'     => 'ap_membership_expires',
            'meta_value'   => $now,
            'meta_compare' => '<=',
        ]);

        foreach ($expired as $user) {
            if (in_array('administrator', (array) $user->roles, true)) {
                // Keep admin rights when membership expires
                $user->add_role('subscriber');
            } else {
                $user->set_role('subscriber');
            }
            update_user_meta($user->ID, 'ap_membership_level', 'Free');

            // Optionally notify
            wp_mail(
                $user->user_email,
                __('Your ArtPulse membership has expired','artpulse'),
                __('Your Pro membership has expired and you have been moved to Free level.','artpulse')
            );
        }
    }
}
