<?php

namespace ArtPulse\Rest;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use ArtPulse\Integration\PortfolioSync;

class SubmissionRestController
{
    /**
     * Allowed post types for submission.
     */
    protected static array $allowed_post_types = [
        'artpulse_event',
        'artpulse_artist',
        'artpulse_artwork',
        'artpulse_org',
    ];

    /**
     * Register the submission endpoint.
     */
    public static function register(): void
    {
        register_rest_route(
            'artpulse/v1',
            '/submissions',
            [
                'methods'             => 'POST',
                'callback'            => [ self::class, 'handle_submission' ],
                'permission_callback' => [ self::class, 'check_permissions' ],
                'args'                => self::get_endpoint_args(),
            ]
        );

        // Optional: GET handler to avoid 404 if frontend pings it
        register_rest_route(
            'artpulse/v1',
            '/submissions',
            [
                'methods'             => 'GET',
                'callback'            => fn() => rest_ensure_response(['message' => 'Use POST to submit a form.']),
                'permission_callback' => '__return_true',
            ]
        );
    }

    /**
     * Alias to maintain consistency with other controllers.
     */
    public static function register_routes(): void
    {
        self::register();
    }

    /**
     * Permission callback for the submission endpoint.
     */
    public static function check_permissions( WP_REST_Request $request ): bool
    {
        $nonce = $request->get_header( 'X-WP-Nonce' ) ?: $request->get_param( '_wpnonce' );

        if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            return false;
        }

        // Allow any logged in user with upload permissions to submit content.
        // Members are granted this capability on registration so they can
        // request upgrades without needing edit privileges.
        return current_user_can( 'upload_files' );
    }

    /**
     * Handle the form submission via REST.
     */
    public static function handle_submission( WP_REST_Request $request ): WP_REST_Response|WP_Error
    {
        $params    = $request->get_json_params();
        $post_type = sanitize_key( $params['post_type'] ?? 'artpulse_event' );

        $user    = wp_get_current_user();
        $is_artist = in_array('artist', (array) $user->roles, true);
        $status = ( 'artpulse_org' === $post_type || ( 'artpulse_artist' === $post_type && ! $is_artist ) ) ? 'pending' : 'publish';

        $meta_fields = self::get_meta_fields_for( $post_type );
        $meta_input  = [];
        $boolean_fields = [ 'event_featured', 'for_sale' ];
        foreach ( $meta_fields as $field_key => $meta_key ) {
            if ( isset( $params[ $field_key ] ) ) {
                $value = $params[ $field_key ];
                if ( in_array( $field_key, $boolean_fields, true ) ) {
                    $meta_input[ $meta_key ] = rest_sanitize_boolean( $value );
                } else {
                    $meta_input[ $meta_key ] = sanitize_text_field( $value );
                }
            }
        }

        $post_id = wp_insert_post( [
            'post_type'   => $post_type,
            'post_title'  => sanitize_text_field( $params['title'] ),
            'post_status' => $status,
            'post_author' => $user->ID,
            'meta_input'  => $meta_input,
        ], true );

        if ( is_wp_error( $post_id ) ) {
            return $post_id;
        }

        if ( 'artpulse_org' === $post_type && 'pending' === $status ) {
            update_user_meta( $user->ID, 'ap_pending_organization_id', $post_id );
        }


        if ( isset( $params['event_type'] ) ) {
            $term_id = absint( $params['event_type'] );
            if ( $term_id ) {
                wp_set_post_terms( $post_id, [ $term_id ], 'event_type' );
            }
        }

        $saved_image_ids = [];
        if ( ! empty( $params['image_ids'] ) && is_array( $params['image_ids'] ) ) {
            $ids       = array_slice( array_map( 'absint', $params['image_ids'] ), 0, 5 );
            $valid_ids = array_filter( $ids, fn( $id ) => get_post_type( $id ) === 'attachment' );
            if ( $valid_ids ) {
                if ( ! empty( $params['image_order'] ) && is_array( $params['image_order'] ) ) {
                    $indices = range( 0, count( $valid_ids ) - 1 );
                    $order   = array_values( array_unique( array_intersect( array_map( 'absint', $params['image_order'] ), $indices ) ) );
                    foreach ( $indices as $idx ) {
                        if ( ! in_array( $idx, $order, true ) ) {
                            $order[] = $idx;
                        }
                    }
                    $reordered = [];
                    foreach ( $order as $i ) {
                        if ( isset( $valid_ids[ $i ] ) ) {
                            $reordered[] = $valid_ids[ $i ];
                        }
                    }
                    $valid_ids = $reordered;
                }
                update_post_meta( $post_id, '_ap_submission_images', $valid_ids );
                set_post_thumbnail( $post_id, $valid_ids[0] );
                $saved_image_ids = $valid_ids;
            }
        }

        if ( in_array( $post_type, [ 'artpulse_artist', 'artpulse_artwork', 'artpulse_event' ], true ) ) {
            $post = get_post( $post_id );
            PortfolioSync::sync_portfolio( $post_id, $post );
        }

        $post       = get_post( $post_id );
        $image_urls = array_values(array_filter(array_map(
            fn( $id ) => wp_get_attachment_url( $id ),
            $saved_image_ids
        )));

        return rest_ensure_response( [
            'id'        => $post_id,
            'title'     => $post->post_title,
            'content'   => $post->post_content,
            'link'      => get_permalink( $post_id ),
            'type'      => $post->post_type,
            'image_ids' => $saved_image_ids,
            'images'    => $image_urls,
        ] );
    }

    /**
     * Schema arguments for endpoint validation.
     */
    public static function get_endpoint_args(): array
    {
        return [
            'post_type' => [
                'type'        => 'string',
                'required'    => true,
                'description' => 'The type of post to create.',
                'enum'        => self::$allowed_post_types,
            ],
            'title' => [
                'type'        => 'string',
                'required'    => true,
                'description' => 'Title of the post.',
            ],
            'artist_name' => [
                'type'        => 'string',
                'required'    => false,
                'description' => 'Artist name to store as meta.',
            ],
            'ead_org_name' => [
                'type'        => 'string',
                'required'    => false,
                'description' => 'Organization name to store as meta.',
            ],
            'event_date' => [
                'type'        => 'string',
                'format'      => 'date',
                'required'    => false,
                'description' => 'Date of the event.',
            ],
            'event_location' => [
                'type'        => 'string',
                'required'    => false,
                'description' => 'Location of the event.',
            ],
            'event_start_date' => [
                'type'        => 'string',
                'format'      => 'date',
                'required'    => false,
                'description' => 'Start date of the event.',
            ],
            'event_end_date' => [
                'type'        => 'string',
                'format'      => 'date',
                'required'    => false,
                'description' => 'End date of the event.',
            ],
            'venue_name' => [
                'type'        => 'string',
                'required'    => false,
                'description' => 'Venue name.',
            ],
            'event_street_address' => [
                'type'        => 'string',
                'required'    => false,
                'description' => 'Street address for the event.',
            ],
            'event_city' => [
                'type'        => 'string',
                'required'    => false,
                'description' => 'City for the event.',
            ],
            'event_state' => [
                'type'        => 'string',
                'required'    => false,
                'description' => 'State or region for the event.',
            ],
            'event_country' => [
                'type'        => 'string',
                'required'    => false,
                'description' => 'Country for the event.',
            ],
            'event_postcode' => [
                'type'        => 'string',
                'required'    => false,
                'description' => 'Postal code for the event.',
            ],
            'event_organizer_name' => [
                'type'        => 'string',
                'required'    => false,
                'description' => 'Name of the event organizer.',
            ],
            'event_organizer_email' => [
                'type'        => 'string',
                'required'    => false,
                'description' => 'Email for the event organizer.',
            ],
            'event_banner_id' => [
                'type'        => 'integer',
                'required'    => false,
                'description' => 'Attachment ID of the event banner.',
            ],
            'event_featured' => [
                'type'        => 'boolean',
                'required'    => false,
                'description' => 'Whether the event should be featured.',
            ],
            'event_type' => [
                'type'        => 'integer',
                'required'    => false,
                'description' => 'Term ID for the event type.',
            ],
            'for_sale' => [
                'type'        => 'boolean',
                'required'    => false,
                'description' => 'Whether the artwork is available for sale.',
            ],
            'price' => [
                'type'        => 'string',
                'required'    => false,
                'description' => 'Asking price for the artwork.',
            ],
            'buy_link' => [
                'type'        => 'string',
                'required'    => false,
                'description' => 'URL to purchase the artwork.',
            ],
            'address_components' => [
                'type'        => 'string',
                'required'    => false,
                'description' => 'Structured address components JSON.',
            ],
            'image_ids' => [
                'type'        => 'array',
                'items'       => [
                    'type' => 'integer',
                ],
                'required'    => false,
                'description' => 'List of image attachment IDs.',
            ],
            'image_order' => [
                'type'        => 'array',
                'items'       => [ 'type' => 'integer' ],
                'required'    => false,
                'description' => 'Optional order of image indexes.',
            ],
        ];
    }

    /**
     * Map field keys to meta keys for each post type.
     */
    private static function get_meta_fields_for( string $post_type ): array
    {
        return match ( $post_type ) {
            'artpulse_event'   => [
                'event_date'           => '_ap_event_date',
                'event_location'       => '_ap_event_location',
                'event_start_date'     => 'event_start_date',
                'event_end_date'       => 'event_end_date',
                'venue_name'           => 'venue_name',
                'event_street_address' => 'event_street_address',
                'event_city'           => 'event_city',
                'event_state'          => 'event_state',
                'event_country'        => 'event_country',
                'event_postcode'       => 'event_postcode',
                'event_organizer_name'  => 'event_organizer_name',
                'event_organizer_email' => 'event_organizer_email',
                'event_banner_id'       => 'event_banner_id',
                'event_featured'        => 'event_featured',
                'address_components'    => 'address_components',
            ],
            'artpulse_artist'  => [
                'artist_bio' => '_ap_artist_bio',
                'artist_org' => '_ap_artist_org',
                'artist_name' => 'artist_name',
            ],
            'artpulse_artwork' => [
                'artwork_medium'     => '_ap_artwork_medium',
                'artwork_dimensions' => '_ap_artwork_dimensions',
                'artwork_materials'  => '_ap_artwork_materials',
                'for_sale'           => 'for_sale',
                'price'              => 'price',
                'buy_link'           => 'buy_link',
            ],
            'artpulse_org'     => [
                'ead_org_name'              => 'ead_org_name',
                'address_components'           => 'address_components',
                'ead_org_description'           => 'ead_org_description',
                'ead_org_website_url'           => 'ead_org_website_url',
                'ead_org_logo_id'               => 'ead_org_logo_id',
                'ead_org_banner_id'             => 'ead_org_banner_id',
                'ead_org_type'                  => 'ead_org_type',
                'ead_org_size'                  => 'ead_org_size',
                'ead_org_facebook_url'          => 'ead_org_facebook_url',
                'ead_org_twitter_url'           => 'ead_org_twitter_url',
                'ead_org_instagram_url'         => 'ead_org_instagram_url',
                'ead_org_linkedin_url'          => 'ead_org_linkedin_url',
                'ead_org_artsy_url'             => 'ead_org_artsy_url',
                'ead_org_pinterest_url'         => 'ead_org_pinterest_url',
                'ead_org_youtube_url'           => 'ead_org_youtube_url',
                'ead_org_primary_contact_name'  => 'ead_org_primary_contact_name',
                'ead_org_primary_contact_email' => 'ead_org_primary_contact_email',
                'ead_org_primary_contact_phone' => 'ead_org_primary_contact_phone',
                'ead_org_primary_contact_role'  => 'ead_org_primary_contact_role',
                'ead_org_street_address'        => 'ead_org_street_address',
                'ead_org_postal_address'        => 'ead_org_postal_address',
                'ead_org_venue_address'         => 'ead_org_venue_address',
                'ead_org_venue_email'           => 'ead_org_venue_email',
                'ead_org_venue_phone'           => 'ead_org_venue_phone',
                'ead_org_monday_start_time'     => 'ead_org_monday_start_time',
                'ead_org_monday_end_time'       => 'ead_org_monday_end_time',
                'ead_org_monday_closed'         => 'ead_org_monday_closed',
                'ead_org_tuesday_start_time'    => 'ead_org_tuesday_start_time',
                'ead_org_tuesday_end_time'      => 'ead_org_tuesday_end_time',
                'ead_org_tuesday_closed'        => 'ead_org_tuesday_closed',
                'ead_org_wednesday_start_time'  => 'ead_org_wednesday_start_time',
                'ead_org_wednesday_end_time'    => 'ead_org_wednesday_end_time',
                'ead_org_wednesday_closed'      => 'ead_org_wednesday_closed',
                'ead_org_thursday_start_time'   => 'ead_org_thursday_start_time',
                'ead_org_thursday_end_time'     => 'ead_org_thursday_end_time',
                'ead_org_thursday_closed'       => 'ead_org_thursday_closed',
                'ead_org_friday_start_time'     => 'ead_org_friday_start_time',
                'ead_org_friday_end_time'       => 'ead_org_friday_end_time',
                'ead_org_friday_closed'         => 'ead_org_friday_closed',
                'ead_org_saturday_start_time'   => 'ead_org_saturday_start_time',
                'ead_org_saturday_end_time'     => 'ead_org_saturday_end_time',
                'ead_org_saturday_closed'       => 'ead_org_saturday_closed',
                'ead_org_sunday_start_time'     => 'ead_org_sunday_start_time',
                'ead_org_sunday_end_time'       => 'ead_org_sunday_end_time',
                'ead_org_sunday_closed'         => 'ead_org_sunday_closed',
            ],
            default            => [],
        };
    }
}
