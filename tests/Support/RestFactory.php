<?php
namespace ArtPulse\Tests;

use WP_REST_Request;
use WP_REST_Server;
use WP_User;

/**
 * Factory helpers for REST API tests.
 */
final class RestFactory {
    /**
     * Ensure the artpulse_event post type is registered.
     */
    private static function ensure_event_cpt(): void {
        if ( post_type_exists( 'artpulse_event' ) ) {
            return;
        }

        register_post_type(
            'artpulse_event',
            array(
                'public'       => true,
                'show_in_rest' => true,
                'supports'     => array( 'title', 'editor', 'author', 'thumbnail' ),
            )
        );

        if ( ! taxonomy_exists( 'event_type' ) ) {
            register_taxonomy(
                'event_type',
                'artpulse_event',
                array(
                    'public'       => true,
                    'show_in_rest' => true,
                    'hierarchical' => true,
                )
            );
        }
    }

    /**
     * Create a core post for tests.
     */
    public static function post( array $args = array() ): int {
        $defaults = array(
            'post_type'   => 'post',
            'post_status' => 'publish',
            'post_title'  => 'Post ' . wp_generate_uuid4(),
        );

        return wp_insert_post( array_merge( $defaults, $args ) );
    }

    /**
     * Create an artpulse_event post.
     */
    public static function event( array $args = array() ): int {
        self::ensure_event_cpt();

        $defaults = array(
            'post_type'   => 'artpulse_event',
            'post_status' => 'publish',
            'post_title'  => 'Event ' . wp_generate_uuid4(),
        );

        return wp_insert_post( array_merge( $defaults, $args ) );
    }

    /**
     * Seed event meta with deterministic location data.
     */
    public static function seed_event_meta( int $id, array $meta = array() ): void {
        $defaults = array(
            'event_lat' => '40.71',
            'event_lng' => '-74.00',
        );

        foreach ( array_merge( $defaults, $meta ) as $key => $value ) {
            update_post_meta( $id, $key, $value );
        }
    }

    /**
     * Create a term in the supplied taxonomy.
     */
    public static function term( string $taxonomy, array $args = array() ): int {
        if ( ! taxonomy_exists( $taxonomy ) ) {
            register_taxonomy(
                $taxonomy,
                array( 'post' ),
                array(
                    'public'       => true,
                    'show_in_rest' => true,
                    'hierarchical' => true,
                )
            );
        }

        $defaults = array(
            'slug' => sanitize_title( wp_generate_uuid4() ),
        );

        $result = wp_insert_term(
            $args['name'] ?? ( 'Term ' . wp_generate_uuid4() ),
            $taxonomy,
            array_merge( $defaults, $args )
        );

        if ( is_wp_error( $result ) ) {
            return 0;
        }

        return (int) $result['term_id'];
    }

    /**
     * Set user meta.
     */
    public static function user_meta( int $user, string $key, $value ): void {
        update_user_meta( $user, $key, $value );
    }

    /**
     * Switch current user to an administrator.
     */
    public static function as_admin(): int {
        $login = 'rest_admin';
        $id    = username_exists( $login );
        if ( ! $id ) {
            $id = wp_create_user( $login, 'password', $login . '@example.com' );
        }
        $user = new WP_User( (int) $id );
        $user->set_role( 'administrator' );
        wp_set_current_user( (int) $id );
        return (int) $id;
    }

    /**
     * Switch current user to a given role.
     */
    public static function as_user( string $role ): int {
        $login = $role . '_user';
        $id    = username_exists( $login );
        if ( ! $id ) {
            $id = wp_create_user( $login, 'password', $login . '@example.com' );
        }
        $user = new WP_User( (int) $id );
        $user->set_role( $role );
        wp_set_current_user( (int) $id );
        return (int) $id;
    }

    /**
     * Dispatch a REST request.
     */
    public static function dispatch( WP_REST_Request $request ) {
        $server = rest_get_server();
        if ( ! $server instanceof WP_REST_Server ) {
            $server                    = new WP_REST_Server();
            $GLOBALS['wp_rest_server'] = $server;
            do_action( 'rest_api_init', $server );
        }
        return $server->dispatch( $request );
    }
}
