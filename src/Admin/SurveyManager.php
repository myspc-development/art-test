<?php
namespace ArtPulse\Admin;

/**
 * Handles post-event surveys and responses.
 */
class SurveyManager
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/event/(?P<id>\\d+)/survey', [
            'methods'  => ['GET', 'POST'],
            'callback' => [self::class, 'handle'],
            'permission_callback' => [self::class, 'check_permission'],
            'args' => ['id' => ['validate_callback' => 'absint']],
        ]);
    }

    public static function check_permission()
    {
        if (!current_user_can('read')) {
            return new \WP_Error('rest_forbidden', __('Unauthorized.', 'artpulse'), ['status' => 403]);
        }
        return true;
    }

    public static function handle(\WP_REST_Request $request)
    {
        $event_id = absint($request->get_param('id'));
        if (!$event_id) {
            return new \WP_Error('invalid_event', 'Invalid event.', ['status' => 400]);
        }

        if ($request->get_method() === 'GET') {
            $responses = get_post_meta($event_id, 'ap_survey_responses', true);
            return rest_ensure_response(is_array($responses) ? $responses : []);
        }

        $answers = (array) $request->get_param('answers');
        if (empty($answers)) {
            return new \WP_Error('invalid_data', 'No answers provided.', ['status' => 400]);
        }

        $responses = get_post_meta($event_id, 'ap_survey_responses', true);
        if (!is_array($responses)) {
            $responses = [];
        }
        $responses[] = [
            'user_id' => get_current_user_id(),
            'answers' => $answers,
        ];
        update_post_meta($event_id, 'ap_survey_responses', $responses);

        do_action('artpulse_survey_submitted', get_current_user_id(), $event_id, $answers);

        return rest_ensure_response(['submitted' => true]);
    }
}
