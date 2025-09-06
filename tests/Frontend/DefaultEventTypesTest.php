<?php
namespace ArtPulse\Frontend\Tests;

use WP_UnitTestCase;
use ArtPulse\Taxonomies\TaxonomiesRegistrar;

/**
 * @group FRONTEND
 */
class DefaultEventTypesTest extends WP_UnitTestCase {
	public function set_up() {
			parent::set_up();
			unregister_taxonomy( 'event_type' );
			TaxonomiesRegistrar::register_event_types();
			delete_option( TaxonomiesRegistrar::EVENT_TYPES_OPTION );
	}

	public function tear_down() {
			unregister_taxonomy( 'event_type' );
			parent::tear_down();
	}

	public function test_default_event_types_inserted_once(): void {
			$this->assertFalse( get_option( TaxonomiesRegistrar::EVENT_TYPES_OPTION ) );

			$result_first = TaxonomiesRegistrar::maybe_insert_default_event_types();
			$this->assertTrue( $result_first );
			$this->assertTrue( (bool) get_option( TaxonomiesRegistrar::EVENT_TYPES_OPTION ) );

			$terms = get_terms(
				array(
					'taxonomy'   => 'event_type',
					'hide_empty' => false,
				)
			);
			$this->assertCount( 8, $terms );

			$result_second = TaxonomiesRegistrar::maybe_insert_default_event_types();
			$this->assertFalse( $result_second );
			$terms_again = get_terms(
				array(
					'taxonomy'   => 'event_type',
					'hide_empty' => false,
				)
			);
			$this->assertCount( 8, $terms_again );
	}
}
