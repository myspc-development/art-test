<?php
namespace ArtPulse\Tests;

use ArtPulse\Core\DashboardWidgetRegistry;

final class SeedWidgets {
	public static function widgetAlpha(): string {
		return '<p>alpha</p>'; }
	public static function widgetBeta(): string {
		return '<p>beta</p>'; }
	public static function widgetGamma(): string {
		return '<p>gamma</p>'; }
	public static function widgetShared(): string {
		return '<p>shared</p>'; }

	public static function bootstrap(): void {
		DashboardWidgetRegistry::register(
			'widget_alpha',
			'Alpha',
			'',
			'',
			array( self::class, 'widgetAlpha' ),
			array(
				'roles'   => array( 'member' ),
				'group'   => 'insights',
				'section' => 'one',
			)
		);
		DashboardWidgetRegistry::register(
			'widget_beta',
			'Beta',
			'',
			'',
			array( self::class, 'widgetBeta' ),
			array(
				'roles'   => array( 'artist' ),
				'group'   => 'insights',
				'section' => 'two',
			)
		);
		DashboardWidgetRegistry::register(
			'widget_gamma',
			'Gamma',
			'',
			'',
			array( self::class, 'widgetGamma' ),
			array(
				'roles'   => array( 'organization' ),
				'group'   => 'actions',
				'section' => 'one',
			)
		);
		DashboardWidgetRegistry::register(
			'widget_shared',
			'Shared',
			'',
			'',
			array( self::class, 'widgetShared' ),
			array(
				'roles'   => array( 'member', 'artist', 'organization' ),
				'group'   => 'actions',
				'section' => 'two',
			)
		);
		DashboardWidgetRegistry::register(
			'widget_demo',
			'Demo',
			'',
			'',
			'__return_null',
			array(
				'settings' => array(
					array(
						'key'     => 'title',
						'type'    => 'string',
						'default' => '',
					),
					array(
						'key'     => 'enabled',
						'type'    => 'boolean',
						'default' => false,
					),
				),
			)
		);

		add_filter( 'pre_option_ap_widget_group_visibility', array( self::class, 'groupVisibility' ) );
		add_filter( 'pre_option_artpulse_role_layout_member', array( self::class, 'memberLayout' ) );
		add_filter( 'pre_option_artpulse_role_layout_artist', array( self::class, 'artistLayout' ) );
		add_filter( 'pre_option_artpulse_role_layout_organization', array( self::class, 'organizationLayout' ) );
	}

	public static function groupVisibility(): array {
		return array(
			'insights' => true,
			'actions'  => true,
		);
	}

	public static function memberLayout(): array {
		return array( array( 'id' => 'widget_alpha' ), array( 'id' => 'widget_shared' ) );
	}

	public static function artistLayout(): array {
		return array( array( 'id' => 'widget_beta' ), array( 'id' => 'widget_shared' ) );
	}

	public static function organizationLayout(): array {
		return array( array( 'id' => 'widget_gamma' ), array( 'id' => 'widget_shared' ) );
	}
}
