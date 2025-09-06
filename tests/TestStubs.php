<?php
namespace ArtPulse\Tests\Stubs {

	class MockStorage {
		public static array $users         = array();
		public static array $user_meta     = array();
		public static array $options       = array();
		public static array $post_meta     = array();
		public static array $json          = array();
		public static bool $have_posts     = true;
		public static array $removed       = array();
		public static array $notice        = array();
		public static array $current_roles = array();
		public static string $screen       = 'dashboard';
	}
}

namespace ArtPulse\Frontend\Tests {
		use ArtPulse\Tests\Stubs\MockStorage;

		// Define loop helpers in the test namespace so that unqualified calls
		// inside templates included during tests resolve here instead of to
		// WordPress's global implementations. This ensures the stubbed loop
		// behaviour is always used and lets tests observe state transitions
		// via MockStorage::$have_posts regardless of whether WordPress has
		// already defined these functions.
	function have_posts(): bool {
			return MockStorage::$have_posts;
	}

	function the_post(): void {
			MockStorage::$have_posts = false;
	}

	function the_title(): void {
			echo 'Test Org';
	}

	function the_content(): void {
			echo 'Org Content';
	}

	function the_post_thumbnail( $size, $attr = array() ): void {}

	function get_the_ID(): int {
			return 1;
	}

	function get_post_meta( $id, $key, $single = false ) {
			return MockStorage::$post_meta[ $key ] ?? '';
	}
}

namespace {
		use ArtPulse\Tests\Stubs\MockStorage;

		// Flag to allow templates to detect when they are being executed within
		// the test suite so they can call namespaced helpers instead of
		// WordPress's loop functions.
	if ( ! defined( 'WP_ENV_FOR_TESTS' ) ) {
			define( 'WP_ENV_FOR_TESTS', true );
	}

	if ( ! isset( $GLOBALS['options'] ) ) {
			$GLOBALS['options'] = array();
	}

	if ( ! function_exists( 'nsl_init' ) ) {
		function nsl_init() {}
	}

	if ( ! function_exists( 'get_user_meta' ) ) {
		function get_user_meta( $uid, $key, $single = false ) {
			return MockStorage::$user_meta[ $uid ][ $key ] ?? '';
		}
	}
	if ( ! function_exists( 'update_user_meta' ) ) {
		function update_user_meta( $uid, $key, $value ) {
			MockStorage::$user_meta[ $uid ][ $key ] = $value;
		}
	}
	if ( ! function_exists( 'delete_user_meta' ) ) {
		function delete_user_meta( $uid, $key ) {
			unset( MockStorage::$user_meta[ $uid ][ $key ] );
		}
	}
	if ( ! function_exists( 'get_option' ) ) {
		function get_option( $key, $default = false ) {
			return MockStorage::$options[ $key ] ?? $default;
		}
	}
	if ( ! function_exists( 'update_option' ) ) {
		function update_option( $key, $value ) {
				$GLOBALS['options'][ $key ]   = $value;
				MockStorage::$options[ $key ] = $value;
		}
	}
	if ( ! function_exists( 'get_userdata' ) ) {
		function get_userdata( $uid ) {
			return MockStorage::$users[ $uid ] ?? null;
		}
	}
	if ( ! class_exists( 'WP_User' ) ) {
		class WP_User {
			public $ID;
			public $roles;
			public function __construct( $ID = 0, $roles = array() ) {
				$this->ID    = $ID;
				$this->roles = $roles;
			}
			public function exists() {
				return true; }
		}
	}
	if ( ! function_exists( 'current_user_can' ) ) {
		function current_user_can( $cap ) {
			return in_array( $cap, MockStorage::$current_roles, true );
		}
	}
	if ( ! function_exists( 'user_can' ) ) {
		function user_can( $user, $cap ) {
			return in_array( $cap, MockStorage::$current_roles, true );
		}
	}
	if ( ! function_exists( 'get_current_user_id' ) ) {
		function get_current_user_id() {
				return 1;
		}
	}
	if ( ! function_exists( 'wp_get_current_user' ) ) {
			/**
			 * Retrieve the current user.
			 *
			 * Returns a minimal object mimicking WP_User with an empty roles array.
			 * Brain Monkey or Patchwork can override this stub as needed during tests.
			 */
		function wp_get_current_user() {
				return (object) array( 'roles' => array() );
		}
	}
	if ( ! function_exists( 'check_ajax_referer' ) ) {
		function check_ajax_referer( $action, $name ) {}
	}
	if ( ! function_exists( 'sanitize_text_field' ) ) {
		function sanitize_text_field( $value ) {
			return $value; }
	}
	if ( ! function_exists( 'sanitize_key' ) ) {
		function sanitize_key( $key ) {
			return preg_replace( '/[^a-z0-9_]/i', '', strtolower( $key ) ); }
	}
	if ( ! function_exists( 'wp_verify_nonce' ) ) {
		function wp_verify_nonce( $nonce, $action = -1 ) {
			return $nonce === 'nonce_' . $action; }
	}
	if ( ! function_exists( 'get_the_title' ) ) {
		function get_the_title( $id = 0 ) {
				return 'Post ' . $id;
		}
	}
	if ( ! function_exists( 'get_permalink' ) ) {
		function get_permalink( $id = 0 ) {
				return '/post/' . $id;
		}
	}
	if ( ! function_exists( 'wp_send_json' ) ) {
		function wp_send_json( $data ) {
			MockStorage::$json = $data; }
	}
	if ( ! function_exists( 'get_header' ) ) {
		function get_header() {}
	}
	if ( ! function_exists( 'get_footer' ) ) {
		function get_footer() {}
	}

		// Provide loop stubs so tests can observe loop state transitions. Use
		// WordPress' native implementations when available.
	if ( ! function_exists( 'have_posts' ) ) {
		function have_posts() {
				return MockStorage::$have_posts;
		}
	}
	if ( ! function_exists( 'the_post' ) ) {
		function the_post() {
				MockStorage::$have_posts = false;
		}
	}

		// If the WordPress test suite is available, let core functions be loaded
		// normally to avoid "cannot redeclare" errors for the remaining stubs.
	if ( defined( 'WP_TESTS_DIR' ) && file_exists( WP_TESTS_DIR . '/includes/bootstrap.php' ) ) {
			return;
	}
	if ( ! function_exists( 'the_post_thumbnail' ) ) {
		function the_post_thumbnail( $size, $attr = array() ) {}
	}
	if ( ! function_exists( 'the_title' ) ) {
		function the_title() {
			echo 'Test Org'; }
	}
	if ( ! function_exists( 'the_content' ) ) {
		function the_content() {
			echo 'Org Content'; }
	}
	if ( ! function_exists( 'get_the_ID' ) ) {
		function get_the_ID() {
				return 1; }
	}
	if ( ! function_exists( 'get_post_type' ) ) {
		function get_post_type( $post = null ) {
				return 'artpulse_org';
		}
	}
	if ( ! function_exists( 'get_post_meta' ) ) {
		function get_post_meta( $id, $key, $single = false ) {
				return MockStorage::$post_meta[ $key ] ?? '';
		}
	}
	if ( ! function_exists( 'esc_html' ) ) {
		function esc_html( $text ) {
			return $text; }
	}
	if ( ! function_exists( 'esc_url' ) ) {
		function esc_url( $url ) {
			return $url; }
	}
	if ( ! function_exists( 'sanitize_title' ) ) {
		function sanitize_title( $title ) {
			return preg_replace( '/[^a-z0-9_\-]+/i', '-', strtolower( $title ) ); }
	}
	if ( ! function_exists( 'wp_script_is' ) ) {
		function wp_script_is( $handle, $list = 'enqueued' ) {
			return false; }
	}
	if ( ! function_exists( 'esc_html__' ) ) {
		function esc_html__( $text, $domain = null ) {
				return $text; }
	}
	if ( ! function_exists( 'esc_html_e' ) ) {
		function esc_html_e( $text, $domain = null ) {
				echo $text;
		}
	}
	if ( ! function_exists( 'remove_meta_box' ) ) {
		function remove_meta_box( $id, $screen, $context ) {
				MockStorage::$removed[] = array( $id, $screen, $context );
		}
	}
	if ( ! function_exists( 'wp_kses_post' ) ) {
		function wp_kses_post( $msg ) {
			return $msg; }
	}
	if ( ! function_exists( 'set_transient' ) ) {
		function set_transient( $k, $v, $e ) {
				MockStorage::$notice = $v; }
	}

	if ( ! function_exists( 'register_activation_hook' ) ) {
		function register_activation_hook( $file, $callback ) {}
	}

	if ( ! function_exists( 'do_action' ) ) {
		function do_action( $hook, ...$args ) {}
	}
	if ( ! function_exists( 'get_transient' ) ) {
		function get_transient( $k ) {
			return null; }
	}
	if ( ! function_exists( 'delete_transient' ) ) {
		function delete_transient( $k ) {}
	}
	if ( ! function_exists( 'esc_attr' ) ) {
		function esc_attr( $t ) {
			return $t; }
	}
	if ( ! function_exists( 'get_current_screen' ) ) {
		function get_current_screen() {
			return (object) array( 'id' => MockStorage::$screen ); }
	}
	// Core hook functions (add_filter/add_action/etc.) are intentionally
	// omitted here so that Brain Monkey can intercept them during tests.
	// Defining them would bypass the monkey patching and break expectations.
	if ( ! function_exists( '__' ) ) {
		function __( $text, $domain = null ) {
			return $text; }
	}
	if ( ! function_exists( 'did_action' ) ) {
		function did_action( $tag ) {
				return 0; }
	}
	if ( ! function_exists( 'plugin_dir_path' ) ) {
		function plugin_dir_path( $file ) {
				return rtrim( dirname( $file ), '/\\' ) . '/';
		}
	}
	if ( ! function_exists( 'get_query_var' ) ) {
		function get_query_var( $key ) {
				return $_GET[ $key ] ?? null;
		}
	}
	if ( ! function_exists( 'set_query_var' ) ) {
		function set_query_var( $key, $value ) {
				$_GET[ $key ] = $value;
		}
	}
	if ( ! function_exists( 'admin_url' ) ) {
		function admin_url( $path = '', $scheme = 'admin' ) {
				return 'https://example.test/wp-admin/' . ltrim( $path, '/' ); }
	}
	if ( ! function_exists( 'add_query_arg' ) ) {
		function add_query_arg( ...$args ) {
				return '#'; }
	}
	if ( ! function_exists( 'remove_query_arg' ) ) {
		function remove_query_arg( $k ) {
			return ''; }
	}
	if ( ! function_exists( 'wp_safe_redirect' ) ) {
		function wp_safe_redirect( $url ) {}
	}
	if ( ! function_exists( 'get_users' ) ) {
		function get_users( $args = array() ) {
			return array_keys( MockStorage::$users ); }
	}
	if ( ! function_exists( 'absint' ) ) {
		function absint( $n ) {
			return (int) $n; }
	}
}
