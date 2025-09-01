<?php

namespace ArtPulse\Frontend {

class StubState {

public static array $post_meta             = array();
public static array $meta_log              = array();
public static array $get_posts_return      = array();
public static array $get_posts_args        = array();
public static array $wp_update_post_args   = array();
public static array $wp_set_post_terms_args = array();
public static array $json                  = array();
public static $json_error                  = null;
public static $deleted_post                = null;
public static array $media_returns         = array();
public static $media_default               = 1;
public static array $inserted_post         = array();
public static $wp_insert_post_return       = 1;
public static int $thumbnail               = 0;
public static array $shortcodes            = array();
public static array $current_user_caps     = array();
public static $page                        = null;
public static string $notice               = '';
public static array $function_exists_map   = array();
public static array $terms_return          = array();
public static array $post_types            = array();

public static function reset(): void {
self::$post_meta            = array();
self::$meta_log             = array();
self::$get_posts_return     = array();
self::$get_posts_args       = array();
self::$wp_update_post_args  = array();
self::$wp_set_post_terms_args = array();
self::$json                 = array();
self::$json_error           = null;
self::$deleted_post         = null;
self::$media_returns        = array();
self::$media_default        = 1;
self::$inserted_post        = array();
self::$wp_insert_post_return = 1;
self::$thumbnail            = 0;
self::$shortcodes           = array();
self::$current_user_caps    = array();
self::$page                 = null;
self::$notice               = '';
self::$function_exists_map  = array();
self::$terms_return         = array();
self::$post_types           = array();
}
}

	if ( ! function_exists( __NAMESPACE__ . '\\get_post' ) ) {
		function get_post( $id ) {
			return (object) array(
				'ID'          => $id,
				'post_title'  => 'Event ' . $id,
				'post_type'   => 'artpulse_artwork',
				'post_author' => 1,
			);
		}
	}

	if ( ! function_exists( __NAMESPACE__ . '\\get_permalink' ) ) {
		function get_permalink( $id ) {
			return '/post/' . $id; }
	}

	if ( ! function_exists( __NAMESPACE__ . '\\get_the_title' ) ) {
		function get_the_title( $postOrId ) {
			if ( is_object( $postOrId ) ) {
				return $postOrId->post_title ?? ( 'Event ' . ( $postOrId->ID ?? '' ) );
			}
			return 'Event ' . $postOrId;
		}
	}

        if ( ! function_exists( __NAMESPACE__ . '\\get_posts' ) ) {
                function get_posts( $args = array() ) {
                        StubState::$get_posts_args = $args;
                        return StubState::$get_posts_return;
                }
        }

        if ( ! function_exists( __NAMESPACE__ . '\\get_post_meta' ) ) {
                function get_post_meta( $post_id, $key, $single = false ) {
                        return StubState::$post_meta[ $post_id ][ $key ] ?? '';
                }
        }

	if ( ! function_exists( __NAMESPACE__ . '\\update_post_meta' ) ) {
		function update_post_meta( $post_id, $key, $value ) {
			StubState::$post_meta[ $post_id ][ $key ] = $value;
			StubState::$meta_log[]                    = array( $post_id, $key, $value );
			return true;
		}
	}

	if ( ! function_exists( __NAMESPACE__ . '\\delete_post_meta' ) ) {
		function delete_post_meta( $post_id, $key ) {
			unset( StubState::$post_meta[ $post_id ][ $key ] );
			StubState::$meta_log[] = array( $post_id, $key, null );
			return true;
		}
	}

	if ( ! function_exists( __NAMESPACE__ . '\\wpautop' ) ) {
		function wpautop( $t ) {
			return $t; }
	}

	if ( ! function_exists( __NAMESPACE__ . '\\sanitize_title' ) ) {
		function sanitize_title( $s ) {
			return $s; }
	}

        if ( ! function_exists( __NAMESPACE__ . '\\shortcode_atts' ) ) {
                function shortcode_atts( $pairs, $atts, $tag = null ) {
                        return array_merge( $pairs, $atts ); }
        }

        if ( ! function_exists( __NAMESPACE__ . '\\wp_list_pluck' ) ) {
                function wp_list_pluck( $input, $field ) {
                        return array_map( function ( $i ) use ( $field ) {
                                return is_object( $i ) ? $i->$field : $i[ $field ];
                        }, $input );
                }
        }

	if ( ! function_exists( __NAMESPACE__ . '\\is_user_logged_in' ) ) {
		function is_user_logged_in() {
			return true; }
	}

	if ( ! function_exists( __NAMESPACE__ . '\\get_current_user_id' ) ) {
		function get_current_user_id() {
			return 1; }
	}

	if ( ! function_exists( __NAMESPACE__ . '\\wp_verify_nonce' ) ) {
		function wp_verify_nonce( $nonce, $action ) {
			return true; }
	}

	if ( ! function_exists( __NAMESPACE__ . '\\check_ajax_referer' ) ) {
		function check_ajax_referer( $action, $name ) {}
	}

        if ( ! function_exists( __NAMESPACE__ . '\\current_user_can' ) ) {
                function current_user_can( $cap, $id = 0 ) {
                        return StubState::$current_user_caps[ $cap ] ?? true; }
        }

	if ( ! function_exists( __NAMESPACE__ . '\\esc_html' ) ) {
		function esc_html( $text ) {
			return $text; }
	}

	if ( ! function_exists( __NAMESPACE__ . '\\esc_url' ) ) {
		function esc_url( $url ) {
			return $url; }
	}

	if ( ! function_exists( __NAMESPACE__ . '\\absint' ) ) {
		function absint( $n ) {
			return (int) $n; }
	}

	if ( ! function_exists( __NAMESPACE__ . '\\esc_attr' ) ) {
		function esc_attr( $t ) {
			return $t; }
	}

        if ( ! function_exists( __NAMESPACE__ . '\\wp_enqueue_script' ) ) {
                function wp_enqueue_script( $handle ) {}
        }

        if ( ! function_exists( __NAMESPACE__ . '\\wp_delete_post' ) ) {
                function wp_delete_post( $id, $force = false ) {
                        StubState::$deleted_post = $id;
                }
        }

        if ( ! function_exists( __NAMESPACE__ . '\\wp_send_json_success' ) ) {
                function wp_send_json_success( $data ) {
                        StubState::$json = $data;
                }
        }

        if ( ! function_exists( __NAMESPACE__ . '\\wp_send_json_error' ) ) {
                function wp_send_json_error( $data ) {
                        StubState::$json_error = $data;
                }
        }

        if ( ! function_exists( __NAMESPACE__ . '\\do_shortcode' ) ) {
                function do_shortcode( $code ) {
                        return StubState::$shortcodes[ $code ] ?? '';
                }
        }

        if ( ! function_exists( __NAMESPACE__ . '\\get_page_by_path' ) ) {
                function get_page_by_path( $path, $output = null, $type = null ) {
                        return StubState::$page;
                }
        }

        if ( ! function_exists( __NAMESPACE__ . '\\wp_get_attachment_url' ) ) {
                function wp_get_attachment_url( $id ) {
                        return 'img' . $id . '.jpg';
                }
        }

        if ( ! function_exists( __NAMESPACE__ . '\\get_post_type' ) ) {
                function get_post_type( $id ) {
                        return StubState::$post_types[ $id ] ?? 'artpulse_event';
                }
        }

        if ( ! function_exists( __NAMESPACE__ . '\\wp_update_post' ) ) {
                function wp_update_post( $arr ) {
                        StubState::$wp_update_post_args = $arr;
                }
        }

        if ( ! function_exists( __NAMESPACE__ . '\\media_handle_upload' ) ) {
                function media_handle_upload( $field, $post_id ) {
                        return StubState::$media_returns[ $field ] ?? StubState::$media_default;
                }
        }

        if ( ! function_exists( __NAMESPACE__ . '\\wp_insert_post' ) ) {
                function wp_insert_post( $arr ) {
                        StubState::$inserted_post = $arr;
                        return StubState::$wp_insert_post_return;
                }
        }

        if ( ! function_exists( __NAMESPACE__ . '\\set_post_thumbnail' ) ) {
                function set_post_thumbnail( $post_id, $thumb ) {
                        StubState::$thumbnail = $thumb;
                }
        }

        if ( ! function_exists( __NAMESPACE__ . '\\get_post_thumbnail_id' ) ) {
                function get_post_thumbnail_id( $post_id ) {
                        return StubState::$thumbnail;
                }
        }

        if ( ! function_exists( __NAMESPACE__ . '\\wc_add_notice' ) ) {
                function wc_add_notice( $msg, $type = '' ) {
                        StubState::$notice = $msg;
                }
        }

        if ( ! function_exists( __NAMESPACE__ . '\\wp_die' ) ) {
                function wp_die( $msg ) {
                        StubState::$notice = $msg;
                }
        }

        if ( ! function_exists( __NAMESPACE__ . '\\function_exists' ) ) {
                function function_exists( $name ) {
                        return StubState::$function_exists_map[ $name ] ?? \function_exists( $name );
                }
        }

        if ( ! function_exists( __NAMESPACE__ . '\\get_terms' ) ) {
                function get_terms( $tax, $args = array() ) {
                        return StubState::$terms_return[ $tax ] ?? array();
                }
        }

        if ( ! function_exists( __NAMESPACE__ . '\\wp_set_post_terms' ) ) {
                function wp_set_post_terms( $id, $terms, $tax ) {
                        StubState::$wp_set_post_terms_args = array( $id, $terms, $tax );
                }
        }

	if ( ! function_exists( __NAMESPACE__ . '\\is_wp_error' ) ) {
		function is_wp_error( $obj ) {
			return $obj instanceof \WP_Error; }
	}

	if ( ! function_exists( __NAMESPACE__ . '\\wp_unslash' ) ) {
		function wp_unslash( $value ) {
			return $value; }
	}

	if ( ! function_exists( __NAMESPACE__ . '\\paginate_links' ) ) {
		function paginate_links( $args ) {
			return ''; }
	}

	if ( ! function_exists( __NAMESPACE__ . '\\add_query_arg' ) ) {
		function add_query_arg( ...$args ) {
			return ''; }
	}

	if ( ! function_exists( __NAMESPACE__ . '\\selected' ) ) {
		function selected( $val, $cmp, $echo = true ) {
			return $val == $cmp ? 'selected' : ''; }
	}

	if ( ! function_exists( __NAMESPACE__ . '\\sanitize_text_field' ) ) {
		function sanitize_text_field( $value ) {
			return is_string( $value ) ? trim( $value ) : $value; }
	}

	if ( ! function_exists( __NAMESPACE__ . '\\wp_kses_post' ) ) {
		function wp_kses_post( $value ) {
			return $value; }
	}

	if ( ! function_exists( __NAMESPACE__ . '\\sanitize_email' ) ) {
		function sanitize_email( $value ) {
			return $value; }
	}

	if ( ! function_exists( __NAMESPACE__ . '\\sanitize_key' ) ) {
		function sanitize_key( $key ) {
			return $key; }
	}

	if ( ! function_exists( __NAMESPACE__ . '\\get_option' ) ) {
		function get_option( $key, $default = false ) {
			return $default; }
	}

	if ( ! function_exists( __NAMESPACE__ . '\\wp_localize_script' ) ) {
		function wp_localize_script( ...$args ) {}
	}

	if ( ! function_exists( __NAMESPACE__ . '\\get_edit_post_link' ) ) {
		function get_edit_post_link( $id ) {
			return '/edit/' . $id; }
	}

	if ( ! function_exists( __NAMESPACE__ . '\\wp_create_nonce' ) ) {
		function wp_create_nonce( $action ) {
			return 'nonce'; }
	}
}
