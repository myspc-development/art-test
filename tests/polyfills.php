<?php
if ( defined( 'UNIT_TESTS_USE_POLYFILLS' ) && ! function_exists( 'plugin_dir_path' ) ) {
        function plugin_dir_path( $file ) {
                return rtrim( dirname( $file ), '/\\' ) . '/';
        }
}
if ( ! function_exists( 'plugin_dir_url' ) ) {
        function plugin_dir_url( $file ) {
                return 'https://example.com/plugin/';
        }
}
