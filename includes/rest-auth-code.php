<?php
if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

add_filter(
        'rest_authorization_required_code',
        static function ( $code ) {
                return is_user_logged_in() ? 403 : 401;
        }
);
