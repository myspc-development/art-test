<?php

namespace ArtPulse\Rest;


class RestRoutes
{
    public static function register()
    {
        add_action('rest_api_init', function () {
            if (!ap_rest_route_registered('artpulse/v1', '/orgs')) {
                register_rest_route('artpulse/v1', '/orgs', [
                'methods'             => 'GET',
                'callback'            => [self::class, 'get_orgs'],
                'permission_callback' => function () {
                    return current_user_can('read');
                },
            ]);
            }

            // ✅ Register the new SubmissionRestController endpoint
            \ArtPulse\Rest\SubmissionRestController::register();
            // Register favorites endpoint so frontend can toggle favorites
            \ArtPulse\Rest\FavoriteRestController::register();
            // Register import endpoint for CSV uploads
            \ArtPulse\Rest\ImportRestController::register();
            // Register template endpoints for CSV imports
            \ArtPulse\Rest\ImportTemplateController::register();
            // Templates for budgets and impact reports
            \ArtPulse\Rest\ReportTemplateController::register();
            // Register organization user management endpoints
            \ArtPulse\Rest\UserInvitationController::register();
            // Register RSVP endpoints for events
            \ArtPulse\Rest\RsvpRestController::register();
            // Feedback endpoints for suggestions and voting
            \ArtPulse\Rest\FeedbackRestController::register();
            // Provide event card markup via REST
            \ArtPulse\Rest\EventCardController::register();
            // Artist-specific events for dashboards
            \ArtPulse\Rest\ArtistEventsController::register();
            \ArtPulse\Rest\ArtistOverviewController::register();
            \ArtPulse\Rest\StatusController::register();
            \ArtPulse\Rest\WidgetLayoutController::register();
            \ArtPulse\Rest\RoleMatrixController::register();
            \ArtPulse\Rest\UpdateDiagnosticsController::register();
        });

        $post_types = ['artpulse_event', 'artpulse_artist', 'artpulse_artwork', 'artpulse_org'];

        foreach ($post_types as $type) {
            add_action("save_post_{$type}", function () use ($type) {
                delete_transient('ap_rest_posts_' . $type);
            });
        }
    }

    public static function get_orgs()
    {
        return self::get_posts_with_meta('artpulse_org', [
            'address' => 'ead_org_street_address',
            'website' => 'ead_org_website_url',
        ]);
    }

    private static function get_posts_with_meta($post_type, $meta_keys = [], array $query_args = [])
    {
        $transient_key = 'ap_rest_posts_' . $post_type;
        $use_cache     = empty($query_args);

        if ($use_cache) {
            $cached = get_transient($transient_key);
            if (false !== $cached) {
                return $cached;
            }
        }

        $posts  = get_posts(array_merge([
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            // Fetch IDs only and skip FOUND_ROWS for a faster query.
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ], $query_args));

        $output = [];

        foreach ($posts as $post_id) {
            $item = [
                'id'      => $post_id,
                'title'   => get_the_title($post_id),
                'content' => apply_filters('the_content', get_post_field('post_content', $post_id)),
                'link'    => get_permalink($post_id),
            ];

            foreach ($meta_keys as $field => $meta_key) {
                $item[$field] = get_post_meta($post_id, $meta_key, true);
            }

            $output[] = $item;
        }

        if ($use_cache) {
            set_transient($transient_key, $output, HOUR_IN_SECONDS);
        }

        return $output;
    }

}
