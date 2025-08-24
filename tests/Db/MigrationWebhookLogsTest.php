<?php
namespace ArtPulse\Db\Tests;

use WP_UnitTestCase;
use ArtPulse\Admin\WebhookLogsPage;
use ArtPulse\Integration\WebhookManager;

class MigrationWebhookLogsTest extends WP_UnitTestCase
{
    public function set_up(): void
    {
        parent::set_up();
        global $wpdb;
        $table = $wpdb->prefix . 'ap_webhook_logs';
        $wpdb->query("DROP TABLE IF EXISTS $table");
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            event VARCHAR(50) NULL,
            payload TEXT NULL,
            status VARCHAR(20) NULL,
            response TEXT NULL,
            created_at DATETIME NULL,
            PRIMARY KEY (id)
        ) $charset";
        $wpdb->query($sql);
        $wpdb->insert($table, [
            'event'      => 'legacy',
            'payload'    => '{}',
            'status'     => '200',
            'response'   => 'OK',
            'created_at' => '2024-01-01 00:00:00',
        ]);
    }

    public function test_migration_transforms_table_and_apis_work(): void
    {
        global $wpdb;
        do_action('artpulse_upgrade', '0.0.1', '0.0.2');

        $table = $wpdb->prefix . 'ap_webhook_logs';
        $cols = $wpdb->get_col("SHOW COLUMNS FROM $table", 0);

        foreach (['subscription_id', 'status_code', 'response_body', 'timestamp'] as $col) {
            $this->assertContains($col, $cols);
        }
        $this->assertNotContains('event', $cols);
        $this->assertNotContains('payload', $cols);

        $idx = $wpdb->get_var("SHOW INDEX FROM $table WHERE Key_name = 'sub_id'");
        $this->assertNotNull($idx);

        $row = $wpdb->get_row("SELECT * FROM $table LIMIT 1", ARRAY_A);
        $this->assertSame('200', $row['status_code']);
        $this->assertSame('OK', $row['response_body']);
        $this->assertSame('2024-01-01 00:00:00', $row['timestamp']);

        WebhookManager::insert_log_for_tests(123, '201', 'Created');
        $this->assertEmpty($wpdb->last_error, $wpdb->last_error);

        ob_start();
        WebhookLogsPage::render();
        ob_end_clean();
        $this->assertEmpty($wpdb->last_error, $wpdb->last_error);
    }

    /**
     * @dataProvider schemaShapes
     */
    public function test_migration_is_idempotent_and_handles_shapes(callable $createShape): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_webhook_logs';
        $wpdb->query("DROP TABLE IF EXISTS {$table}");
        $createShape($wpdb, $table);

        do_action('artpulse_upgrade');
        do_action('artpulse_upgrade'); // run again (idempotent)

        $cols = $wpdb->get_col("SHOW COLUMNS FROM {$table}", 0);
        $this->assertEqualsCanonicalizing(
            ['id', 'subscription_id', 'status_code', 'response_body', 'timestamp'],
            array_values(array_intersect($cols, ['id', 'subscription_id', 'status_code', 'response_body', 'timestamp']))
        );

        $idx = $wpdb->get_results("SHOW INDEX FROM {$table}");
        $this->assertSame(1, count(array_filter($idx, fn($i) => $i->Key_name === 'sub_id')));

        WebhookManager::insert_log_for_tests(42, '201', 'Created');
        $this->assertEmpty($wpdb->last_error, $wpdb->last_error);

        ob_start();
        WebhookLogsPage::render();
        $html = ob_get_clean();
        $this->assertStringContainsString('201', $html);
        $this->assertStringContainsString('42', $html);
    }

    public function test_migration_handles_large_response_body(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_webhook_logs';
        $wpdb->query("DROP TABLE IF EXISTS {$table}");
        $wpdb->query("CREATE TABLE {$table} (
            id bigint unsigned auto_increment primary key,
            event varchar(191), payload longtext, status varchar(20),
            response longtext, created_at datetime
        )");
        $huge = str_repeat('X', 80 * 1024); // 80KB
        $wpdb->insert($table, [
            'event' => 'ping',
            'payload' => '{}',
            'status' => '200',
            'response' => $huge,
            'created_at' => current_time('mysql'),
        ]);
        do_action('artpulse_upgrade');
        $row = $wpdb->get_row("SELECT response_body FROM {$table} ORDER BY id DESC LIMIT 1");
        $this->assertNotEmpty($row->response_body);
        $this->assertTrue(strlen($row->response_body) >= 65535 || strlen($row->response_body) === strlen($huge));
        ob_start();
        WebhookLogsPage::render();
        $html = ob_get_clean();
        $this->assertMatchesRegularExpression('/(&hellip;|\\.\\.\\.|…)/', $html);
    }

    public static function schemaShapes(): array
    {
        return [
            'legacy'  => [function ($wpdb, $t) {
                $wpdb->query("CREATE TABLE $t (id bigint unsigned auto_increment primary key, event varchar(191), payload longtext, status varchar(20), response longtext, created_at datetime)");
            }],
            'partial' => [function ($wpdb, $t) {
                $wpdb->query("CREATE TABLE $t (id bigint unsigned auto_increment primary key, subscription_id bigint, response_body text, created_at datetime)");
            }],
            'missing' => [function ($wpdb, $t) {
                // do nothing; migration should create table
            }],
        ];
    }
}
