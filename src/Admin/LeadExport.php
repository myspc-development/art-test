<?php
namespace ArtPulse\Admin;

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

class LeadExport {
        public static function register(): void {
                add_action( 'admin_post_ap_export_leads', array( self::class, 'handle' ) );
        }

        public static function handle(): void {
                if ( ! current_user_can( 'manage_options' ) ) {
                        wp_die( __( 'Unauthorized', 'artpulse' ) );
                }

                check_admin_referer( 'ap_export_leads' );

                $rows = self::get_leads();

                header( 'Content-Type: text/csv' );
                header( 'Content-Disposition: attachment; filename="artpulse-leads.csv"' );

                $fh = fopen( 'php://output', 'w' );
                fputcsv( $fh, array( 'Name', 'Email' ) );
                foreach ( $rows as $row ) {
                        fputcsv( $fh, array( $row['name'], $row['email'] ) );
                }
                fclose( $fh );
                exit;
        }

        private static function get_leads(): array {
                global $wpdb;
                $table = $wpdb->prefix . 'ap_leads';
                $results = $wpdb->get_results( "SELECT name, email FROM {$table} ORDER BY id DESC", ARRAY_A );
                return $results ?: array();
        }
}
