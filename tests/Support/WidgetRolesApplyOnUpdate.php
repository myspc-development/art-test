<?php
declare(strict_types=1);

namespace ArtPulse\Tests;

final class WidgetRolesApplyOnUpdate {
	public static function register(): void {
		add_action( 'updated_option', array( self::class, 'onUpdated' ), 10, 3 );
		add_action( 'added_option', array( self::class, 'onAdded' ), 10, 2 );
	}

	private static function apply( $value ): void {
		if ( ! is_array( $value ) || ! class_exists( \ArtPulse\Core\DashboardWidgetRegistry::class ) ) {
			return;
		}
		try {
			$ref  = new \ReflectionClass( \ArtPulse\Core\DashboardWidgetRegistry::class );
			$prop = $ref->getProperty( 'widgets' );
			$prop->setAccessible( true );
			$widgets = $prop->getValue();
			if ( ! is_array( $widgets ) ) {
				return;
			}
			foreach ( $value as $id => $conf ) {
				if ( ! isset( $widgets[ $id ] ) || ! is_array( $conf ) ) {
					continue;
				}
				if ( array_key_exists( 'roles', $conf ) ) {
					$roles = $conf['roles'];
					if ( $roles === null ) {
						$widgets[ $id ]['roles'] = null;
					} elseif ( is_array( $roles ) ) {
						$widgets[ $id ]['roles'] = array_values( array_unique( array_map( 'strval', $roles ) ) );
					} else {
						$widgets[ $id ]['roles'] = array( (string) $roles );
					}
				}
				if ( array_key_exists( 'capability', $conf ) ) {
					$widgets[ $id ]['capability'] = is_string( $conf['capability'] ) ? $conf['capability'] : '';
				}
			}
			$prop->setValue( null, $widgets );
		} catch ( \Throwable $e ) {
			// silent
		}
	}

	public static function onUpdated( $option, $old, $new ): void {
		if ( $option === 'artpulse_widget_roles' ) {
			self::apply( $new );
		}
	}

	public static function onAdded( $option, $value ): void {
		if ( $option === 'artpulse_widget_roles' ) {
			self::apply( $value );
		}
	}
}
