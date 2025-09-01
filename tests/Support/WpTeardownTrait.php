<?php
namespace ArtPulse\Tests;

/**
 * Utility trait to clean up WordPress state between tests.
 */
trait WpTeardownTrait {
    /**
     * Remove users, posts, transients and options to avoid leaking state
     * between tests. All calls are guarded to work when WordPress is not
     * loaded.
     */
    protected function reset_wp_state(): void {
        // Users.
        if (function_exists('get_users') && function_exists('wp_delete_user')) {
            foreach (get_users(['fields' => 'ID']) as $user_id) {
                wp_delete_user($user_id);
            }
        }

        // Posts.
        if (function_exists('get_posts') && function_exists('wp_delete_post')) {
            $posts = get_posts([
                'post_type'   => 'any',
                'post_status' => 'any',
                'numberposts' => -1,
                'fields'      => 'ids',
            ]);
            foreach ($posts as $post_id) {
                wp_delete_post($post_id, true);
            }
        }

        // Transients.
        if (function_exists('delete_transient')) {
            global $wpdb;
            if (isset($wpdb) && method_exists($wpdb, 'get_col')) {
                $transients = $wpdb->get_col(
                    "SELECT option_name FROM {$wpdb->options} " .
                    "WHERE option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%'"
                );
                foreach ($transients as $name) {
                    $key = str_replace(['_transient_', '_site_transient_'], '', $name);
                    delete_transient($key);
                    delete_site_transient($key);
                }
            }
        }

        // Options.
        if (function_exists('delete_option')) {
            global $wpdb;
            if (isset($wpdb) && method_exists($wpdb, 'get_col')) {
                $options = $wpdb->get_col(
                    "SELECT option_name FROM {$wpdb->options} " .
                    "WHERE option_name NOT LIKE '_transient_%' " .
                    "AND option_name NOT LIKE '_site_transient_%'"
                );
                foreach ($options as $option) {
                    delete_option($option);
                }
            }
        }
    }
}
