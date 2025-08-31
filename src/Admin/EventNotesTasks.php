<?php
namespace ArtPulse\Admin;

use WP_REST_Request;
use WP_REST_Response;

class EventNotesTasks {

	public static function register(): void {
		add_action( 'add_meta_boxes', array( self::class, 'add_meta_boxes' ) );
		add_action( 'save_post_artpulse_event', array( self::class, 'handle_save' ), 10, 2 );
		add_action( 'init', array( self::class, 'maybe_install_tables' ) );
		add_action( 'rest_api_init', array( self::class, 'register_rest' ) );
	}

	public static function add_meta_boxes(): void {
		add_meta_box( 'ap_event_notes', __( 'Internal Notes', 'artpulse' ), array( self::class, 'render_notes_box' ), 'artpulse_event' );
		add_meta_box( 'ap_event_tasks', __( 'Tasks', 'artpulse' ), array( self::class, 'render_tasks_box' ), 'artpulse_event' );
	}

	public static function render_notes_box( \WP_Post $post ): void {
		echo '<textarea name="ap_new_note" rows="3" placeholder="' . esc_attr__( 'Add note...', 'artpulse' ) . '"></textarea>';
		$notes = self::get_notes( $post->ID );
		if ( $notes ) {
			echo '<ul>';
			foreach ( $notes as $n ) {
				$user   = get_userdata( $n['user_id'] );
				$author = $user ? $user->display_name : $n['user_id'];
				echo '<li><strong>' . esc_html( $author ) . '</strong> (' . esc_html( $n['created_at'] ) . ') - ' . esc_html( $n['note'] ) . '</li>';
			}
			echo '</ul>';
		}
	}

	public static function render_tasks_box( \WP_Post $post ): void {
		echo '<p><input type="text" name="ap_task_title" placeholder="' . esc_attr__( 'Task title', 'artpulse' ) . '"></p>';
		echo '<p><label>' . __( 'Assignee', 'artpulse' ) . ': <input type="number" name="ap_task_assignee"></label></p>';
		echo '<p><label>' . __( 'Due Date', 'artpulse' ) . ': <input type="date" name="ap_task_due" ></label></p>';
		$tasks = self::get_tasks( $post->ID );
		if ( $tasks ) {
			echo '<ul>';
			foreach ( $tasks as $t ) {
				$user = $t['assignee'] ? get_userdata( $t['assignee'] ) : null;
				$name = $user ? $user->display_name : __( 'Unassigned', 'artpulse' );
				echo '<li>' . esc_html( $t['title'] ) . ' - ' . esc_html( $name ) . ' (' . esc_html( $t['status'] ) . ')</li>';
			}
			echo '</ul>';
		}
	}

	public static function handle_save( int $post_id, \WP_Post $post ): void {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( $note = trim( $_POST['ap_new_note'] ?? '' ) ) {
			self::add_note( $post_id, get_current_user_id(), $note );
		}
		if ( ! empty( $_POST['ap_task_title'] ) ) {
			$title    = sanitize_text_field( $_POST['ap_task_title'] );
			$assignee = intval( $_POST['ap_task_assignee'] ?? 0 );
			$due      = sanitize_text_field( $_POST['ap_task_due'] ?? '' );
			self::add_task( $post_id, $title, $assignee, $due );
		}
	}

	public static function maybe_install_tables(): void {
		global $wpdb;
		$notes   = $wpdb->prefix . 'ap_event_notes';
		$tasks   = $wpdb->prefix . 'ap_event_tasks';
		$charset = $wpdb->get_charset_collate();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		if ( ! $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $notes ) ) ) {
			dbDelta(
				"CREATE TABLE $notes (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (id),
                event_id BIGINT NOT NULL,
                user_id BIGINT NOT NULL,
                note TEXT NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL DEFAULT NULL,
                KEY event_id (event_id)
            ) $charset"
			);
		}
		if ( ! $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tasks ) ) ) {
			dbDelta(
				"CREATE TABLE $tasks (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (id),
                event_id BIGINT NOT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT NULL,
                assignee BIGINT NULL,
                due_date DATE NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'open',
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL DEFAULT NULL,
                KEY event_id (event_id),
                KEY status (status)
            ) $charset"
			);
		}
	}

	/**
	 * Determine if a table exists in the database.
	 *
	 * These checks avoid queries failing on fresh installs where the
	 * tables may not have been created yet.
	 */
	private static function table_exists( string $table ): bool {
		global $wpdb;
		return (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
	}

	private static function get_notes( int $event_id ): array {
		global $wpdb;
		$event_id = absint( $event_id );
		$table    = $wpdb->prefix . 'ap_event_notes';
		// Ensure the notes table exists to avoid errors on new installs.
		if ( ! self::table_exists( $table ) ) {
			self::maybe_install_tables();
			if ( ! self::table_exists( $table ) ) {
				return array();
			}
		}
		return $wpdb->get_results(
			$wpdb->prepare( 'SELECT id, event_id, user_id, note, created_at, updated_at FROM %i WHERE event_id=%d ORDER BY created_at DESC', $table, $event_id ),
			ARRAY_A
		) ?: array();
	}

	private static function get_tasks( int $event_id ): array {
		global $wpdb;
		$event_id = absint( $event_id );
		$table    = $wpdb->prefix . 'ap_event_tasks';
		// Ensure the tasks table exists to avoid errors on new installs.
		if ( ! self::table_exists( $table ) ) {
			self::maybe_install_tables();
			if ( ! self::table_exists( $table ) ) {
				return array();
			}
		}
		return $wpdb->get_results(
			$wpdb->prepare( 'SELECT id, event_id, title, description, assignee, due_date, status, created_at, updated_at FROM %i WHERE event_id=%d ORDER BY created_at DESC', $table, $event_id ),
			ARRAY_A
		) ?: array();
	}

	private static function add_note( int $event_id, int $user_id, string $note ): void {
		global $wpdb;
		$event_id = absint( $event_id );
		$user_id  = absint( $user_id );
		$table    = $wpdb->prefix . 'ap_event_notes';
		// Skip if the table is missing to prevent insert errors on fresh installs.
		if ( ! self::table_exists( $table ) ) {
			self::maybe_install_tables();
			if ( ! self::table_exists( $table ) ) {
				return;
			}
		}
		$wpdb->insert(
			$table,
			array(
				'event_id'   => $event_id,
				'user_id'    => $user_id,
				'note'       => $note,
				'created_at' => current_time( 'mysql' ),
			)
		);
	}

	private static function add_task( int $event_id, string $title, int $assignee, string $due ): void {
		global $wpdb;
		$event_id = absint( $event_id );
		$assignee = absint( $assignee );
		$table    = $wpdb->prefix . 'ap_event_tasks';
		// Skip if the table is missing to prevent insert errors on fresh installs.
		if ( ! self::table_exists( $table ) ) {
			self::maybe_install_tables();
			if ( ! self::table_exists( $table ) ) {
				return;
			}
		}
		$wpdb->insert(
			$table,
			array(
				'event_id'   => $event_id,
				'title'      => $title,
				'assignee'   => $assignee ?: null,
				'due_date'   => $due ?: null,
				'status'     => 'open',
				'created_at' => current_time( 'mysql' ),
			)
		);
	}

	public static function get_open_count( int $event_id ): int {
		global $wpdb;
		$event_id = absint( $event_id );
		$table    = $wpdb->prefix . 'ap_event_tasks';
		// Avoid SELECT errors if tables have not been created yet.
		if ( ! self::table_exists( $table ) ) {
			self::maybe_install_tables();
			if ( ! self::table_exists( $table ) ) {
				return 0;
			}
		}
		return (int) $wpdb->get_var(
			$wpdb->prepare( 'SELECT COUNT(*) FROM %i WHERE event_id=%d AND status=%s', $table, $event_id, 'open' )
		);
	}

	public static function register_rest(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/event/(?P<id>\d+)/notes' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/event/(?P<id>\d+)/notes',
				array(
					'methods'             => 'GET',
					'callback'            => function ( WP_REST_Request $req ) {
						$id = intval( $req['id'] );
						if ( ! current_user_can( 'edit_post', $id ) ) {
							return new WP_REST_Response( array(), 403 );
						}
						return \rest_ensure_response( self::get_notes( $id ) );
					},
					'permission_callback' => function ( WP_REST_Request $req ) {
						$id = intval( $req['id'] );
						if ( ! current_user_can( 'edit_post', $id ) ) {
							return new \WP_Error( 'rest_forbidden', __( 'Unauthorized.', 'artpulse' ), array( 'status' => 403 ) );
						}
						return true;
					},
				)
			);
		}
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/event/(?P<id>\d+)/tasks' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/event/(?P<id>\d+)/tasks',
				array(
					'methods'             => 'GET',
					'callback'            => function ( WP_REST_Request $req ) {
						$id = intval( $req['id'] );
						if ( ! current_user_can( 'edit_post', $id ) ) {
							return new WP_REST_Response( array(), 403 );
						}
						return \rest_ensure_response( self::get_tasks( $id ) );
					},
					'permission_callback' => function ( WP_REST_Request $req ) {
						$id = intval( $req['id'] );
						if ( ! current_user_can( 'edit_post', $id ) ) {
							return new \WP_Error( 'rest_forbidden', __( 'Unauthorized.', 'artpulse' ), array( 'status' => 403 ) );
						}
						return true;
					},
				)
			);
		}
	}
}
