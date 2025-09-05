<?php
namespace ArtPulse\Migrations\Tests;

use WP_UnitTestCase;

final class IdempotencyTest extends WP_UnitTestCase {
	private function snapshot_db(): array {
		global $wpdb;
		$tables = $wpdb->get_col('SHOW TABLES');
		$hashes = [];
		foreach ($tables as $t) {
			$rows = $wpdb->get_results("SELECT * FROM $t", ARRAY_A);
			$hashes[$t] = md5(json_encode($rows));
		}
                return $hashes;
        }

        public function test_unify_webhook_logs_is_idempotent(): void {
                run_migration('2025_08_23_unify_webhook_logs');
                $first = $this->snapshot_db();
                run_migration('2025_08_23_unify_webhook_logs');
                $second = $this->snapshot_db();
                $this->assertSame($first, $second);
        }
}

function run_migration(string $migration): void {
	$func = 'ap_' . preg_replace('/^\\d{4}_\\d{2}_\\d{2}_/', '', $migration) . '_migration';
	if (!function_exists($func)) {
		require_once __DIR__ . "/../../includes/migrations/{$migration}.php";
	}
	if (function_exists($func)) {
		$func();
	}
}
