<?php
use PHPUnit\Framework\TestCase;

class RestRouteConflictsTest extends TestCase
{
    private string $root;
    private string $wpDir;
    private string $script;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
        $this->wpDir = $this->root . '/wordpress';
        if (!is_dir($this->wpDir)) {
            mkdir($this->wpDir);
        }
        copy(__DIR__ . '/fixtures/wp-load.php', $this->wpDir . '/wp-load.php');
        $this->script = $this->root . '/tools/rest-route-conflicts.php';
    }

    protected function tearDown(): void
    {
        if (is_dir($this->wpDir)) {
            array_map('unlink', glob($this->wpDir . '/*'));
            rmdir($this->wpDir);
        }
    }

    public function testDetectsDuplicateRoutesWithDifferentCallbacks(): void
    {
        $cmd = 'PATCHWORK_DISABLE=1 ' . PHP_BINARY . ' ' . escapeshellarg($this->script);
        exec($cmd, $output, $status);
        $result = implode("\n", $output);

        $this->assertSame(1, $status, 'Expected exit code 1 when duplicates exist');
        $this->assertStringContainsString('/duplicate', $result);
        $this->assertStringContainsString('foo_callback', $result);
        $this->assertStringContainsString('bar_callback', $result);
    }

    public function testJsonOutputStructure(): void
    {
        $cmd = 'PATCHWORK_DISABLE=1 ' . PHP_BINARY . ' ' . escapeshellarg($this->script) . ' --json';
        exec($cmd, $output, $status);
        $json = implode("\n", $output);
        $data = json_decode($json, true);

        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $entry = $data[0];
        $this->assertSame('/duplicate', $entry['route']);
        $this->assertSame('GET', $entry['method']);
        $this->assertIsArray($entry['callbacks']);
        $this->assertCount(2, $entry['callbacks']);
        $callbacks = array_column($entry['callbacks'], 'callback');
        sort($callbacks);
        $this->assertSame(['bar_callback', 'foo_callback'], $callbacks);
    }

    public function testSuggestFixOptionEmitsSuggestions(): void
    {
        $cmd = 'PATCHWORK_DISABLE=1 ' . PHP_BINARY . ' ' . escapeshellarg($this->script) . ' --suggest-fix';
        exec($cmd, $output, $status);
        $result = implode("\n", $output);

        $this->assertSame(1, $status);
        $this->assertStringContainsString('# Suggested fix for GET /duplicate (foo_callback)', $result);
        $this->assertStringContainsString('# Suggested fix for GET /duplicate (bar_callback)', $result);
    }
}
