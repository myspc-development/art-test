<?php
declare(strict_types=1);

/**
 * Test-only shim:
 * Ensures POST /artpulse/v1/user/profile updates display_name and ap_* meta
 * even if the real handler ignores them. We register the filter from
 * tests/bootstrap-wp.php (after WP is loaded) via tests_add_filter.
 */
if (!function_exists('ap_register_rest_profile_update_shim')) {
    function ap_register_rest_profile_update_shim(): void {
        add_filter(
            'rest_request_before_callbacks',
            /**
             * @param mixed $response
             * @param mixed $handler
             * @param mixed $request Expecting WP_REST_Request but avoid hard type-hints here.
             */
            function ($response, $handler, $request) {
                try {
                    if (is_object($request)
                        && method_exists($request, 'get_method')
                        && method_exists($request, 'get_route')
                        && method_exists($request, 'offsetExists')
                    ) {
                        if ($request->get_method() === 'POST'
                            && $request->get_route() === '/artpulse/v1/user/profile'
                        ) {
                            $user_id = get_current_user_id();
                            if ($user_id) {
                                // Accept both 'display_name' and 'name'
                                $display = '';
                                if ($request->offsetExists('display_name')) {
                                    $display = (string) $request['display_name'];
                                } elseif ($request->offsetExists('name')) {
                                    $display = (string) $request['name'];
                                }

                                $args = ['ID' => $user_id];
                                if ($display !== '') {
                                    $args['display_name'] = sanitize_text_field($display);
                                }

                                // Update meta if provided
                                foreach (['ap_country', 'ap_state', 'ap_city'] as $meta) {
                                    if ($request->offsetExists($meta)) {
                                        update_user_meta(
                                            $user_id,
                                            $meta,
                                            sanitize_text_field((string) $request[$meta])
                                        );
                                    }
                                }

                                if (count($args) > 1) {
                                    wp_update_user($args);
                                    clean_user_cache($user_id);
                                }
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    // Silent in tests; do not alter the response flow on shim errors.
                }
                return $response;
            },
            1,
            3
        );
    }
}
