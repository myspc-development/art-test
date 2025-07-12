<?php
namespace ArtPulse\Taxonomies;

class SpotlightCategory
{
    public static function register(): void
    {
        register_taxonomy('spotlight_category', 'spotlight', [
            'labels'       => [
                'name'          => __('Spotlight Categories', 'artpulse'),
                'singular_name' => __('Spotlight Category', 'artpulse'),
            ],
            'public'       => true,
            'show_ui'      => true,
            'show_in_rest' => true,
            'hierarchical' => false,
            'rewrite'      => ['slug' => 'spotlight-category'],
        ]);
    }
}
