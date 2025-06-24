<?php
namespace ArtPulse\Core;

use ArtPulse\Core\IntegrationHooks;

class WooCommerceIntegration
{
    public static function register(): void
    {
        // Assign on completion
        add_action('woocommerce_order_status_completed', [ self::class, 'handleCompletedOrder' ], 10, 1);

        // Downgrade on refund or cancel
        add_action('woocommerce_order_status_refunded',  [ self::class, 'handleRefundOrCancel' ], 10, 1);
        add_action('woocommerce_order_status_cancelled', [ self::class, 'handleRefundOrCancel' ], 10, 1);
    }

    public static function handleCompletedOrder( $order_id )
    {
        if ( ! class_exists('WC_Order') ) {
            return;
        }

        $order   = wc_get_order( $order_id );
        $user_id = $order->get_user_id();
        if ( ! $user_id ) {
            return;
        }

        $opts = get_option('artpulse_settings', []);
        $map  = [
            'Basic' => intval( $opts['woo_basic_product_id'] ?? 0 ),
            'Pro'   => intval( $opts['woo_pro_product_id']   ?? 0 ),
            'Org'   => intval( $opts['woo_org_product_id']   ?? 0 ),
        ];

        foreach ( $order->get_items() as $item ) {
            $prod_id = $item->get_product_id();
            foreach ( $map as $level => $product_id ) {
                if ( $product_id && $prod_id === $product_id ) {
                    self::assignMembership( $user_id, $level );
                    break 2;
                }
            }
        }
    }

    public static function handleRefundOrCancel( $order_id )
    {
        if ( ! class_exists('WC_Order') ) {
            return;
        }

        $order   = wc_get_order( $order_id );
        $user_id = $order->get_user_id();
        if ( ! $user_id ) {
            return;
        }

        // Downgrade to Free
        $user = get_userdata( $user_id );
        if (in_array('administrator', (array) $user->roles, true)) {
            // Preserve admin role when adjusting membership
            $user->add_role('subscriber');
        } else {
            $user->set_role('subscriber');
        }
        update_user_meta( $user_id, 'ap_membership_level', 'Free' );
        update_user_meta( $user_id, 'ap_membership_expires', current_time('timestamp') );
        IntegrationHooks::membershipDowngraded( $user_id, 'Free' );

        wp_mail(
            $user->user_email,
            __('Your ArtPulse membership has been cancelled','artpulse'),
            __('We detected a refund or cancellation. You have been moved to Free membership.','artpulse')
        );
    }

    /**
     * Assigns the given level to the user and sets an expiry.
     */
    protected static function assignMembership( $user_id, $level )
    {
        $user = get_userdata( $user_id );
        if (in_array('administrator', (array) $user->roles, true)) {
            // Avoid stripping admin rights while granting membership
            $user->add_role('subscriber');
        } else {
            $user->set_role('subscriber');
        }
        update_user_meta( $user_id, 'ap_membership_level', $level );

        // Determine duration: Basic & Pro 30d, Org 365d
        $days  = in_array( $level, ['Basic','Pro'], true ) ? 30 : 365;
        $expiry = strtotime( "+{$days} days", current_time('timestamp') );
        update_user_meta( $user_id, 'ap_membership_expires', $expiry );
        IntegrationHooks::membershipUpgraded( $user_id, $level );

        wp_mail(
            $user->user_email,
            sprintf( __('Your ArtPulse membership is now %s','artpulse'), $level ),
            sprintf(
                __('Thank you! Your membership level is set to %s and expires on %s.','artpulse'),
                $level,
                date_i18n( get_option('date_format'), $expiry )
            )
        );
    }
}
