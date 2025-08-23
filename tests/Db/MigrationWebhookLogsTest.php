<?php
namespace ArtPulse\Db\Tests;

use WP_UnitTestCase;
use ArtPulse\Admin\WebhookLogsPage;
use ArtPulse\Integration\WebhookManager;
use ReflectionClass;

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

        $ref = new ReflectionClass(WebhookManager::class);
        $method = $ref->getMethod('insert_log');
        $method->setAccessible(true);
        $method->invoke(null, 123, '201', 'Created');
        $this->assertEmpty($wpdb->last_error, $wpdb->last_error);

        ob_start();
        WebhookLogsPage::render();
        ob_end_clean();
        $this->assertEmpty($wpdb->last_error, $wpdb->last_error);
    }
}
