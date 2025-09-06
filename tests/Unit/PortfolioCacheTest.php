<?php
namespace ArtPulse\Tests;

use WP_UnitTestCase;
use function wp_cache_set;
use function wp_cache_get;
use function ap_clear_portfolio_cache;

/**
 * @group UNIT
 */
class PortfolioCacheTest extends WP_UnitTestCase {
	public function test_clears_only_plugin_group(): void {
			// Populate caches in plugin group and another group.
			wp_cache_set( 'foo', 'bar', 'other_group' );
			wp_cache_set( 'a', 'b', 'artpulse_queries' );
			wp_cache_set( 'c', 'd', 'artpulse_queries' );

			// Sanity check entries exist.
			$this->assertSame( 'b', wp_cache_get( 'a', 'artpulse_queries' ) );
			$this->assertSame( 'bar', wp_cache_get( 'foo', 'other_group' ) );

			// Clear a single key.
			ap_clear_portfolio_cache( 'a' );
			$this->assertFalse( wp_cache_get( 'a', 'artpulse_queries' ) );
			$this->assertSame( 'd', wp_cache_get( 'c', 'artpulse_queries' ) );
			$this->assertSame( 'bar', wp_cache_get( 'foo', 'other_group' ) );

			// Clear remaining group.
			ap_clear_portfolio_cache();
			$this->assertFalse( wp_cache_get( 'c', 'artpulse_queries' ) );
			$this->assertSame( 'bar', wp_cache_get( 'foo', 'other_group' ) );
	}
}
