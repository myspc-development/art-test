<?php
namespace ArtPulse\Frontend;

class DashboardRoleRewrite {

	public static function register(): void {
		add_action( 'init', array( self::class, 'add_rules' ) );
		add_filter( 'query_vars', array( self::class, 'register_vars' ) );
		add_action( 'template_redirect', array( self::class, 'maybe_render' ) );
	}

	public static function add_rules(): void {
		add_rewrite_rule(
			'^dashboard-role(?:\\.php)?/?$',
			'index.php?ap_dashboard_role=1&ap_dashboard=1',
			'top'
		);
	}

	public static function register_vars( array $vars ): array {
		$vars[] = 'ap_dashboard_role';
		$vars[] = 'ap_dashboard';
		return $vars;
	}

        public static function maybe_render(): void {
                if ( get_query_var( 'ap_dashboard_role' ) || get_query_var( 'ap_dashboard' ) ) {
                        $role  = null;
                        $nonce = isset( $_GET['ap_preview_nonce'] ) ? sanitize_key( $_GET['ap_preview_nonce'] ) : '';
                        if (
                                isset( $_GET['ap_preview_role'] ) &&
                                current_user_can( 'manage_options' ) &&
                                wp_verify_nonce( $nonce, 'ap_preview' )
                        ) {
                                $role = sanitize_key( $_GET['ap_preview_role'] );
                        }
                        if ( $role ) {
                                \ap_render_dashboard( array( $role ) );
                        } else {
                                \ap_render_dashboard();
                        }
                        $ap_test_mode = defined( 'AP_TEST_MODE' ) ? AP_TEST_MODE : (bool) getenv( 'AP_TEST_MODE' );
                        if (
                                $ap_test_mode ||
                                ( defined( 'WP_RUNNING_TESTS' ) && WP_RUNNING_TESTS )
                        ) {
                                return;
                        }
                        exit;
                }
        }
}
