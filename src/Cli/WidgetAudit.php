<?php
namespace ArtPulse\Cli;

use ArtPulse\Audit\WidgetSources;
use ArtPulse\Audit\Parity;
use ArtPulse\Audit\AuditBus;
use ArtPulse\Core\DashboardRenderer;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Support\WidgetIds;
use WP_CLI; // phpcs:ignore

/**
 * WP-CLI interface for the Widget Audit Suite.
 */
class WidgetAudit {
	public function register(): void {
		if ( ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
				return;
		}
			\WP_CLI::add_command( 'artpulse audit:widgets', array( $this, 'widgets' ) );
			\WP_CLI::add_command( 'artpulse audit:visibility', array( $this, 'visibility' ) );
			\WP_CLI::add_command( 'artpulse audit:builder', array( $this, 'builder' ) );
			\WP_CLI::add_command( 'artpulse audit:render', array( $this, 'render' ) );
			\WP_CLI::add_command( 'artpulse audit:fix', array( $this, 'fix' ) );
	}

		/**
		 * Last generated widget audit rows.
		 *
		 * @var array<int,array<string,string>>
		 */
	public array $rows = array();

		/**
		 * List widget audit data.
		 *
		 * @param array $args  Positional arguments (unused).
		 * @param array $assoc Associative arguments.
		 *
		 * @return array<int,array<string,string>> The unformatted widget data.
		 */
	public function widgets( $args, $assoc ): array {
			$registry = WidgetSources::get_registry();
			$vis      = WidgetSources::get_visibility_roles();
			$hidden   = WidgetSources::get_hidden_for_roles();
			$problems = Parity::problems();
			$rows     = array();
		foreach ( $registry as $id => $info ) {
				$hidden_roles = array();
			foreach ( $hidden as $role => $ids ) {
				if ( in_array( $id, (array) $ids, true ) ) {
						$hidden_roles[] = $role;
				}
			}
				$rows[] = array(
					'id'                      => $id,
					'status'                  => $info['status'],
					'has_callback'            => $info['callback_is_callable'] ? 'yes' : 'no',
					'roles_from_registry'     => implode( ',', $info['roles_from_registry'] ),
					'roles_from_visibility'   => implode( ',', $vis[ $id ] ?? array() ),
					'hidden_for_roles'        => implode( ',', $hidden_roles ),
					'registered_in_code_file' => $info['class'],
					'problem'                 => $problems[ $id ] ?? '',
				);
		}

			// Expose the raw rows for consumers before formatting.
			$this->rows = $rows;

			$format = $assoc['format'] ?? 'table';
			\WP_CLI\Utils\format_items( $format, $rows, array( 'id', 'status', 'has_callback', 'roles_from_registry', 'roles_from_visibility', 'hidden_for_roles', 'registered_in_code_file', 'problem' ) );

			return $rows;
	}

	public function visibility( $args, $assoc ) {
		$vis  = WidgetSources::get_visibility_roles();
		$reg  = WidgetSources::get_registry();
		$rows = array();
		foreach ( $vis as $id => $roles ) {
			$rows[] = array(
				'id'                     => $id,
				'allowed_roles(option)'  => implode( ',', $roles ),
				'registry_roles(if any)' => implode( ',', $reg[ $id ]['roles_from_registry'] ?? array() ),
				'union'                  => implode( ',', array_unique( array_merge( $roles, $reg[ $id ]['roles_from_registry'] ?? array() ) ) ),
				'notes(conflicts)'       => '',
			);
		}
		$format = $assoc['format'] ?? 'table';
		\WP_CLI\Utils\format_items( $format, $rows, array( 'id', 'allowed_roles(option)', 'registry_roles(if any)', 'union', 'notes(conflicts)' ) );
	}

	public function builder( $args, $assoc ) {
		$role = sanitize_key( $assoc['role'] ?? '' );
		if ( ! $role ) {
			WP_CLI::error( 'Missing --role parameter' );
		}
		$roles = $role === 'all' ? array_keys( ( new \WP_Roles() )->roles ) : array( $role );
		$reg   = WidgetSources::get_registry();
		foreach ( $roles as $r ) {
			$layout = WidgetSources::get_builder_layout( $r );
			$rows   = array();
			$i      = 1;
			foreach ( $layout as $id ) {
				$rows[] = array(
					'order'        => $i++,
					'widget_id'    => $id,
					'in_registry'  => isset( $reg[ $id ] ) ? 'yes' : 'no',
					'has_callback' => isset( $reg[ $id ] ) && $reg[ $id ]['callback_is_callable'] ? 'yes' : 'no',
				);
			}
			WP_CLI::line( 'Role: ' . $r );
			$format = $assoc['format'] ?? 'table';
			\WP_CLI\Utils\format_items( $format, $rows, array( 'order', 'widget_id', 'in_registry', 'has_callback' ) );
		}
	}

	public function render( $args, $assoc ) {
		$role = sanitize_key( $assoc['role'] ?? '' );
		if ( ! $role ) {
			WP_CLI::error( 'Missing --role parameter' );
		}
		$context  = sanitize_key( $assoc['context'] ?? 'builder_preview' );
		$simulate = isset( $assoc['simulate-user'] );

		if ( $context === 'builder_preview' ) {
			AuditBus::reset();
			$sources  = new WidgetSources();
			$renderer = new DashboardRenderer();
			$ids      = $sources->builderForRole( $role ) ?: array();
			$renderer->renderIds(
				$ids,
				array(
					'context'    => $simulate ? 'builder_preview_real' : 'builder_preview',
					'gate_caps'  => $simulate,
					'gate_flags' => $simulate,
				)
			);
			$events   = AuditBus::snapshot();
			$rendered = array();
			foreach ( $events as $e ) {
				if ( ( $e['type'] ?? '' ) === 'render' && ! empty( $e['ok'] ) ) {
					$rendered[] = $e['id'];
				}
			}
			$rendered = array_values( array_unique( $rendered ) );
			$missing  = array_values( array_diff( $ids, $rendered ) );
			$extra    = array_values( array_diff( $rendered, $ids ) );
			WP_CLI::line( 'EXPECTED: ' . implode( ', ', $ids ) );
			WP_CLI::line( 'RENDERED: ' . implode( ', ', $rendered ) );
			if ( $missing ) {
				WP_CLI::line( 'MISSING: ' . implode( ', ', $missing ) );
			}
			if ( $extra ) {
				WP_CLI::line( 'EXTRA: ' . implode( ', ', $extra ) );
			}
			WP_CLI::line( 'COUNTS expected=' . count( $ids ) . ' rendered=' . count( $rendered ) . ' missing=' . count( $missing ) . ' extra=' . count( $extra ) );
			if ( $missing || $extra ) {
				WP_CLI::halt( 1 );
			}
			return;
		}

		if ( isset( $assoc['no-preview'] ) ) {
			add_filter( 'ap_dashboard_preview_enabled', '__return_false' );
		}
		add_filter( 'ap_dashboard_hidden_widgets', fn( $hidden, $r ) => array(), 10, 2 );

		// Ensure we have a user of the desired role
		$user_ids = get_users(
			array(
				'role'   => $role,
				'number' => 1,
				'fields' => 'ID',
			)
		);
		if ( $user_ids ) {
			$uid = (int) $user_ids[0];
		} else {
			$uid = wp_insert_user(
				array(
					'user_login' => 'audit_' . $role . '_' . time(),
					'user_pass'  => wp_generate_password(),
					'role'       => $role,
				)
			);
		}
		wp_set_current_user( $uid );

		AuditBus::reset();
		$defs = DashboardWidgetRegistry::get_widgets_by_role( $role, $uid );
		foreach ( array_keys( $defs ) as $id ) {
			DashboardRenderer::render( $id, $uid );
		}
		$report = Parity::compare_with_actual( $role );
		WP_CLI::line( 'EXPECTED: ' . implode( ', ', $report['would_render'] ) );
		WP_CLI::line( 'RENDERED: ' . implode( ', ', $report['did_render'] ) );
		if ( $report['missing'] ) {
			WP_CLI::line( 'MISSING:' );
			foreach ( $report['missing'] as $id => $reason ) {
				WP_CLI::line( " - {$id}: {$reason}" );
			}
		}
		if ( $report['extra'] ) {
			WP_CLI::line( 'EXTRA: ' . implode( ', ', $report['extra'] ) );
		}
		WP_CLI::line( 'COUNTS expected=' . count( $report['would_render'] ) . ' rendered=' . count( $report['did_render'] ) . ' missing=' . count( $report['missing'] ) . ' extra=' . count( $report['extra'] ) );
		if ( $report['missing'] || $report['extra'] || $report['problems'] ) {
			WP_CLI::halt( 1 );
		}
	}

	public function fix( $args, $assoc ) {
		$role     = isset( $assoc['role'] ) ? sanitize_key( $assoc['role'] ) : '';
		$registry = WidgetSources::get_registry();
		$summary  = array();

		if ( isset( $assoc['resolve-parity'] ) ) {
			$target      = $assoc['resolve-parity'];
			$target_role = $target !== true ? sanitize_key( $target ) : '';
			$keys        = array( 'artpulse_widget_roles', 'artpulse_hidden_widgets', 'artpulse_dashboard_layouts' );
			$remapped    = 0;
			$deduped     = 0;
			foreach ( $keys as $k ) {
				$opt = get_option( $k, array() );
				if ( ! is_array( $opt ) ) {
					continue;
				}
				foreach ( $opt as $r => &$ids ) {
					if ( $target_role && $r !== $target_role ) {
						continue;
					}
					if ( $k === 'artpulse_dashboard_layouts' ) {
						$new  = array();
						$seen = array();
						foreach ( (array) $ids as $item ) {
							$id    = is_array( $item ) ? ( $item['id'] ?? '' ) : $item;
							$canon = WidgetIds::canonicalize( $id );
							if ( $canon !== $id ) {
								++$remapped;
							}
							if ( in_array( $canon, $seen, true ) ) {
								++$deduped;
								continue;
							}
							$seen[] = $canon;
							$new[]  = is_array( $item ) ? array_merge( $item, array( 'id' => $canon ) ) : $canon;
						}
						$ids = $new;
					} else {
						$before   = (array) $ids;
						$ids      = array_map(
							function ( $id ) use ( &$remapped ) {
								$canon = WidgetIds::canonicalize( $id );
								if ( $canon !== $id ) {
									$remapped++;
								}
								return $canon;
							},
							$before
						);
						$ids      = array_values( array_unique( $ids ) );
						$deduped += count( $before ) - count( $ids );
					}
				}
				unset( $ids );
				update_option( $k, $opt );
			}
			WP_CLI::line( "remapped={$remapped} deduped={$deduped}" );
		}

		// Unhide widgets for a role.
		if ( isset( $assoc['unhide'] ) ) {
			if ( ! $role ) {
				WP_CLI::error( 'Missing --role for --unhide' );
			}
			$hidden = get_option( 'artpulse_hidden_widgets', array() );
			if ( ! is_array( $hidden ) ) {
				$hidden = array();
			}
			$to_unhide = $hidden[ $role ] ?? array();
			foreach ( (array) $to_unhide as $id ) {
				$summary[] = array(
					'widget' => $id,
					'action' => 'unhide',
					'class'  => '',
				);
				do_action(
					'artpulse_audit_event',
					'fix',
					array(
						'widget' => $id,
						'action' => 'unhide',
						'class'  => null,
					)
				);
			}
			$hidden[ $role ] = array();
			update_option( 'artpulse_hidden_widgets', $hidden );
		}

		// Activate all widgets.
		if ( isset( $assoc['activate-all'] ) ) {
			$flags = get_option( 'artpulse_widget_flags', array() );
			if ( ! is_array( $flags ) ) {
				$flags = array();
			}
			foreach ( $registry as $id => $info ) {
				$flags[ $id ] = array_merge( $flags[ $id ] ?? array(), array( 'status' => 'active' ) );
				$summary[]    = array(
					'widget' => $id,
					'action' => 'activate',
					'class'  => '',
				);
				do_action(
					'artpulse_audit_event',
					'fix',
					array(
						'widget' => $id,
						'action' => 'activate',
						'class'  => null,
					)
				);
			}
			update_option( 'artpulse_widget_flags', $flags );
		}

		$known = array();
		if ( isset( $assoc['bind-known'] ) ) {
			$known = array(
				'widget_news'                => array(
					'class'  => \ArtPulse\Widgets\ArtPulseNewsFeedWidget::class,
					'method' => 'render',
				),
				'widget_recommended_for_you' => array(
					'class'  => \ArtPulse\Widgets\RecommendationsWidget::class,
					'method' => 'render',
				),
			);
		}

		$placeholders = array();
		foreach ( $registry as $id => $info ) {
			if ( empty( $info['is_placeholder'] ) ) {
				continue;
			}
			$placeholders[] = $id;

			// Known bindings.
			if ( isset( $known[ $id ] ) ) {
				$map = $known[ $id ];
				if ( class_exists( $map['class'] ) && method_exists( $map['class'], $map['method'] ) ) {
					DashboardWidgetRegistry::bindRenderer( $id, array( $map['class'], $map['method'] ) );
					$summary[] = array(
						'widget' => $id,
						'action' => 'bind',
						'class'  => $map['class'],
					);
					do_action(
						'artpulse_audit_event',
						'fix',
						array(
							'widget' => $id,
							'action' => 'bind',
							'class'  => $map['class'],
						)
					);
					continue;
				}
			}

			// Fallback auto-bind based on slug.
			$base  = strpos( $id, 'widget_' ) === 0 ? substr( $id, 7 ) : $id;
			$class = 'ArtPulse\\Widgets\\' . str_replace( ' ', '', ucwords( str_replace( '_', ' ', $base ) ) ) . 'Widget';
			if ( class_exists( $class ) && method_exists( $class, 'render' ) ) {
				DashboardWidgetRegistry::bindRenderer( $id, array( $class, 'render' ) );
				$summary[] = array(
					'widget' => $id,
					'action' => 'bind',
					'class'  => $class,
				);
				do_action(
					'artpulse_audit_event',
					'fix',
					array(
						'widget' => $id,
						'action' => 'bind',
						'class'  => $class,
					)
				);
			}
		}

		// Hide placeholder widgets for roles.
		if ( isset( $assoc['hide-placeholders'] ) ) {
			$target      = $assoc['hide-placeholders'];
			$target_role = $target !== true ? sanitize_key( $target ) : ( $role ?: '' );
			$hidden      = get_option( 'artpulse_hidden_widgets', array() );
			if ( ! is_array( $hidden ) ) {
				$hidden = array();
			}
			$roles = $target_role ? array( $target_role ) : array_keys( ( new \WP_Roles() )->roles );
			foreach ( $roles as $r ) {
				$list = $hidden[ $r ] ?? array();
				foreach ( $placeholders as $id ) {
					if ( ! in_array( $id, $list, true ) ) {
						$list[]    = $id;
						$summary[] = array(
							'widget' => $id,
							'action' => 'hide',
							'class'  => '',
						);
						do_action(
							'artpulse_audit_event',
							'fix',
							array(
								'widget' => $id,
								'action' => 'hide',
								'class'  => null,
							)
						);
					}
				}
				$hidden[ $r ] = array_values( array_unique( $list ) );
			}
			update_option( 'artpulse_hidden_widgets', $hidden );
		}

		// Output summary.
		if ( $summary ) {
			\WP_CLI\Utils\format_items( 'table', $summary, array( 'widget', 'action', 'class' ) );
		} else {
			WP_CLI::line( 'No changes made.' );
		}

		// Determine leftover placeholders.
		$hidden   = WidgetSources::get_hidden_for_roles();
		$leftover = array();
		foreach ( $placeholders as $id ) {
			$def            = DashboardWidgetRegistry::get( $id );
			$class          = $def['class'] ?? '';
			$is_placeholder = str_starts_with( $class, 'ArtPulse\\Widgets\\Placeholder\\' );
			if ( ! $is_placeholder ) {
				continue;
			}
			$is_hidden = false;
			foreach ( $hidden as $r => $ids ) {
				if ( in_array( $id, (array) $ids, true ) ) {
					$is_hidden = true;
					break;
				}
			}
			if ( ! $is_hidden ) {
				$leftover[] = $id;
			}
		}

		if ( $leftover ) {
			WP_CLI::warning( 'Unbound placeholders remain: ' . implode( ', ', $leftover ) );
			WP_CLI::halt( 1 );
		}
	}
}
