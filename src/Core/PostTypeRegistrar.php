<?php

namespace ArtPulse\Core;

class PostTypeRegistrar
{
    public static function register()
    {
        // Base config shared by all CPTs
        $common = [
            'public'        => true,
            'show_ui'       => true,
            'show_in_menu'  => true,
            'show_in_rest'  => true,
            'has_archive'   => true,
            'rewrite'       => true,
            'supports'      => ['title', 'editor', 'thumbnail'],
        ];

        // Register CPTs with custom menu icons
        $post_types = [
            'artpulse_event' => [
                'label'      => __('Events', 'artpulse'),
                'rewrite'    => ['slug' => 'events'],
                'taxonomies' => ['event_type'],
                'menu_icon'  => 'dashicons-calendar',
            ],
            'artpulse_artist' => [
                'label'      => __('Artists', 'artpulse'),
                'rewrite'    => ['slug' => 'artists'],
                'supports'   => ['title', 'editor', 'thumbnail', 'custom-fields'],
                'menu_icon'  => 'dashicons-admin-users',
            ],
            'ap_artist_request' => [
                'label'      => __('Artist Upgrade Requests', 'artpulse'),
                'public'     => false,
                'show_ui'    => true,
                'show_in_menu'=> false,
                'show_in_rest'=> true,
                'supports'   => ['title'],
            ],
            'ap_profile_link_req' => [
                'label'       => __('Profile Link Requests', 'artpulse'),
                'public'      => false,
                'show_ui'     => true,
                'show_in_menu'=> true,
                'show_in_rest'=> true,
                'supports'    => ['title'],
                'menu_icon'   => 'dashicons-admin-links',
            ],
            'ap_profile_link' => [
                'label'       => __('Profile Links', 'artpulse'),
                'public'      => false,
                'show_ui'     => true,
                'show_in_menu'=> true,
                'show_in_rest'=> true,
                'supports'    => ['title'],
                'menu_icon'   => 'dashicons-admin-users',
            ],
            'artpulse_artwork' => [
                'label'      => __('Artworks', 'artpulse'),
                'rewrite'    => ['slug' => 'artworks'],
                'supports'   => ['title', 'editor', 'thumbnail', 'custom-fields'],
                'menu_icon'  => 'dashicons-format-image',
            ],
            'artpulse_org' => [
                'label'      => __('Organizations', 'artpulse'),
                'rewrite'    => ['slug' => 'organizations'],
                'menu_icon'  => 'dashicons-building',
            ],
            'ap_event_template' => [
                'label'       => __('Event Templates', 'artpulse'),
                'public'      => false,
                'show_ui'     => true,
                'show_in_menu'=> 'edit.php?post_type=artpulse_event',
                'supports'    => ['title', 'editor'],
                'menu_icon'   => 'dashicons-calendar-alt',
            ],
        ];

        $opts              = get_option('artpulse_settings', []);
        $default_rsvp      = isset($opts['default_rsvp_limit']) ? absint($opts['default_rsvp_limit']) : 50;
        $default_waitlists = !empty($opts['waitlists_enabled']);

        foreach ($post_types as $post_type => $args) {
            $capabilities = self::generate_caps($post_type);
            register_post_type(
                $post_type,
                array_merge(
                    $common,
                    $args,
                    [
                        'capability_type' => $post_type,
                        'map_meta_cap'    => true,
                        'capabilities'    => $capabilities,
                    ]
                )
            );
        }

        // Register Meta Boxes
        self::register_meta_boxes();

        // Register additional post meta
        register_post_meta(
            'artpulse_event',
            '_ap_submission_images',
            [
                'type'         => 'array',
                'single'       => true,
                'show_in_rest' => [
                    'schema' => [
                        'type'  => 'array',
                        'items' => [ 'type' => 'integer' ],
                    ],
                ],
            ]
        );

        // Taxonomies

        register_taxonomy(
            'artpulse_medium',
            'artpulse_artwork',
            [
                'label'        => __('Medium', 'artpulse'),
                'public'       => true,
                'show_in_rest' => true,
                'hierarchical' => true,
                'rewrite'      => ['slug' => 'medium'],
            ]
        );

        register_taxonomy(
            'art_type',
            'artpulse_artwork',
            [
                'label'        => __('Art Types', 'artpulse-community'),
                'public'       => true,
                'show_in_rest' => true,
                'hierarchical' => true,
                'rewrite'      => ['slug' => 'art-type'],
            ]
        );

        add_action('init', [self::class, 'insert_default_art_types'], 21);
    }

    public static function insert_default_art_types() {
        $types = [
            'painting'     => __('Painting', 'artpulse-community'),
            'drawing'      => __('Drawing', 'artpulse-community'),
            'sculpture'    => __('Sculpture', 'artpulse-community'),
            'installation' => __('Installation', 'artpulse-community'),
            'photography'  => __('Photography', 'artpulse-community'),
            'print'        => __('Print', 'artpulse-community'),
            'digital-art'  => __('Digital Art', 'artpulse-community'),
            'video'        => __('Video', 'artpulse-community'),
            'mixed-media'  => __('Mixed Media', 'artpulse-community'),
            'performance'  => __('Performance', 'artpulse-community'),
            'textile'      => __('Textile', 'artpulse-community'),
            'illustration' => __('Illustration', 'artpulse-community'),
            'other'        => __('Other', 'artpulse-community'),
        ];
        foreach ($types as $slug => $name) {
            if (!term_exists($name, 'art_type')) {
                wp_insert_term($name, 'art_type', ['slug' => $slug]);
            }
        }
    }

    private static function register_meta_boxes()
    {
        $opts              = get_option('artpulse_settings', []);
        $default_rsvp      = isset($opts['default_rsvp_limit']) ? absint($opts['default_rsvp_limit']) : 50;
        $default_waitlists = !empty($opts['waitlists_enabled']);

        register_post_meta('artpulse_event', '_ap_event_date', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);

        register_post_meta('artpulse_event', '_ap_event_location', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);

        register_post_meta('artpulse_event', 'event_start_date', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);

        register_post_meta('artpulse_event', 'event_end_date', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);

        register_post_meta('artpulse_event', 'event_recurrence_rule', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);

        register_post_meta('artpulse_event', 'venue_name', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);

        register_post_meta('artpulse_event', 'event_street_address', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);

        register_post_meta('artpulse_event', 'event_city', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);

        register_post_meta('artpulse_event', 'event_state', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);

        register_post_meta('artpulse_event', 'event_country', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);

        register_post_meta('artpulse_event', 'event_postcode', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);

        register_post_meta('artpulse_event', 'event_organizer_name', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);

        register_post_meta('artpulse_event', 'event_organizer_email', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);

        register_post_meta('artpulse_event', '_ap_event_organization', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'integer',
        ]);

        register_post_meta('artpulse_event', '_ap_event_artists', [
            'show_in_rest' => [
                'schema' => [
                    'type'  => 'array',
                    'items' => ['type' => 'integer'],
                ],
            ],
            'single'       => true,
            'type'         => 'array',
        ]);

        register_post_meta('artpulse_event', 'event_banner_id', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'integer',
        ]);

        register_post_meta('artpulse_event', 'event_featured', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'boolean',
        ]);

        register_post_meta('artpulse_event', 'event_rsvp_enabled', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'boolean',
        ]);

        register_post_meta('artpulse_event', 'event_rsvp_limit', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'integer',
            'default'      => $default_rsvp,
        ]);

        register_post_meta('artpulse_event', 'event_waitlist_enabled', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'boolean',
            'default'      => $default_waitlists,
        ]);

        register_post_meta('artpulse_event', 'event_rsvp_list', [
            'show_in_rest' => [
                'schema' => [
                    'type'  => 'array',
                    'items' => [ 'type' => 'integer' ],
                ],
            ],
            'single'       => true,
            'type'         => 'array',
        ]);

        register_post_meta('artpulse_event', 'event_waitlist', [
            'show_in_rest' => [
                'schema' => [
                    'type'  => 'array',
                    'items' => [ 'type' => 'integer' ],
                ],
            ],
            'single'       => true,
            'type'         => 'array',
        ]);

        register_post_meta('artpulse_event', 'event_attended', [
            'show_in_rest' => [
                'schema' => [
                    'type'  => 'array',
                    'items' => [ 'type' => 'integer' ],
                ],
            ],
            'single'       => true,
            'type'         => 'array',
        ]);

        register_post_meta('artpulse_artist', '_ap_artist_bio', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);

        register_post_meta('artpulse_artist', '_ap_artist_org', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'integer',
        ]);

        register_post_meta('artpulse_artwork', '_ap_artwork_medium', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);

        register_post_meta('artpulse_artwork', '_ap_artwork_dimensions', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);

        register_post_meta('artpulse_artwork', '_ap_artwork_materials', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);

        register_post_meta('artpulse_artwork', 'for_sale', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'boolean',
        ]);

        register_post_meta('artpulse_artwork', 'price', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);

        register_post_meta('artpulse_artwork', 'buy_link', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);

        register_post_meta('artpulse_artwork', 'price_history', [
            'single' => true,
            'type'   => 'array',
        ]);

        register_post_meta('artpulse_event', 'address_components', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);

        register_post_meta('artpulse_org', 'address_components', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);
    }

    public static function generate_caps(string $post_type): array
    {
        $plural = $post_type . 's';

        return [
            'edit_post'              => "edit_{$post_type}",
            'read_post'              => "read_{$post_type}",
            'delete_post'            => "delete_{$post_type}",
            'edit_posts'             => "edit_{$plural}",
            'edit_others_posts'      => "edit_others_{$plural}",
            'publish_posts'          => "publish_{$plural}",
            'read_private_posts'     => "read_private_{$plural}",
            'delete_posts'           => "delete_{$plural}",
            'delete_private_posts'   => "delete_private_{$plural}",
            'delete_published_posts' => "delete_published_{$plural}",
            'delete_others_posts'    => "delete_others_{$plural}",
            'edit_private_posts'     => "edit_private_{$plural}",
            'edit_published_posts'   => "edit_published_{$plural}",
            'create_posts'           => "create_{$plural}",
        ];
    }
}
