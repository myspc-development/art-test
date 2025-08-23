<?php
namespace ArtPulse\Core;

use ArtPulse\Admin\OrgCommunicationsCenter;

class Activator
{
    public static function activate()
    {
        if (get_option('artpulse_settings') === false) {
            add_option('artpulse_settings', [
                'basic_fee'             => '',
                'pro_fee'               => '',
                'org_fee'               => '',
                'currency'              => 'USD',
                'stripe_enabled'        => 0,
                'stripe_pub_key'        => '',
                'stripe_secret'         => '',
                'stripe_webhook_secret' => '',
                'woocommerce_enabled'   => 0,
                'service_worker_enabled' => 0,
                'openai_api_key'        => '',
                'external_api_base_url' => '',
                'external_api_token'    => '',
                'debug_logging'         => 0,
                'override_artist_membership'  => 0,
                'override_org_membership'    => 0,
                'override_member_membership' => 0,
                'default_privacy_email'      => 'public',
                'default_privacy_location'   => 'public',
                'email_method'        => 'wp_mail',
                'mailgun_api_key'     => '',
                'mailgun_domain'      => '',
                'sendgrid_api_key'    => '',
                'mailchimp_api_key'   => '',
                'mailchimp_list_id'   => '',
                'email_from_name'     => '',
                'email_from_address'  => '',
                'keep_data_on_uninstall' => 1,
                'enable_wp_admin_access' => 0,
            ]);
        }

        if (get_option('artpulse_webhook_status') === false) {
            add_option('artpulse_webhook_status', 'Not yet received');
        }

        // Ensure role hierarchy table exists and populated
        RoleSetup::maybe_install_table();
        OrgCommunicationsCenter::install_messages_table();
        require_once ARTPULSE_PLUGIN_DIR . 'includes/db-schema.php';
        \ArtPulse\DB\create_rsvp_table();
        \ArtPulse\DB\create_monetization_tables();
        \ArtPulse\Admin\ScheduledMessageManager::install_scheduled_table();

        // Create indexes to speed up membership lookups
        self::maybe_add_meta_indexes();
        self::maybe_add_event_indexes();
    }

    /**
     * Add indexes on usermeta and postmeta for membership keys if missing.
     */
    private static function maybe_add_meta_indexes(): void
    {
        global $wpdb;

        $tables = [
            $wpdb->usermeta => 'ap_usermeta_key_value',
            $wpdb->postmeta => 'ap_postmeta_key_value',
        ];

        foreach ($tables as $table => $index_name) {
            $exists = $wpdb->get_var(
                $wpdb->prepare('SHOW INDEX FROM %i WHERE Key_name = %s', $table, $index_name)
            );
            if (!$exists) {
                $wpdb->query(
                    $wpdb->prepare(
                        'CREATE INDEX %i ON %i (meta_key(191), meta_value(191))',
                        $index_name,
                        $table
                    )
                );
            }
        }
    }

    /**
     * Index event start dates for faster range queries.
     */
    private static function maybe_add_event_indexes(): void
    {
        global $wpdb;
        $index = 'ap_event_start';
        $exists = $wpdb->get_var(
            $wpdb->prepare("SHOW INDEX FROM {$wpdb->postmeta} WHERE Key_name = %s", $index)
        );
        if (!$exists) {
            $wpdb->query(
                $wpdb->prepare(
                    'CREATE INDEX %i ON %i (meta_key(50), meta_value(10))',
                    $index,
                    $wpdb->postmeta
                )
            );
        }
    }
}
