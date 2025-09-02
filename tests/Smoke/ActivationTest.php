<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Minimal smoke: loads the plugin main file and sanity-checks WP boot.
 *
 * Set PLUGIN_MAIN env var to something like:
 *   art-test-main/art-test-main.php
 * or whatever your main file is. If not set, it will try a couple of common guesses.
 */
final class ActivationTest extends WP_UnitTestCase {

    /** @var ?string */
    private $plugin_file;

    public function setUp(): void {
        parent::setUp();

        // Resolve plugin main file from env or common guesses
        $candidates = [];
        $env = getenv('PLUGIN_MAIN');
        if ($env) {
            $candidates[] = $env;
        }
        $pluginDir = basename(dirname(dirname(__DIR__))); // repo root basename (best-effort)
        // Common guesses (adjust if needed)
        $candidates[] = 'art-test-main/art-test-main.php';
        $candidates[] = $pluginDir . '/' . $pluginDir . '.php';

        $this->plugin_file = null;
        foreach ($candidates as $rel) {
            $abs = rtrim(WP_PLUGIN_DIR, '/\\') . '/' . ltrim($rel, '/\\');
            if (is_file($abs)) {
                $this->plugin_file = $abs;
                break;
            }
        }
        $this->assertNotNull($this->plugin_file, "Could not locate plugin main file. Set PLUGIN_MAIN env var.");
    }

    public function test_plugin_loads_without_warnings_or_fatals(): void {
        $this->assertFileExists($this->plugin_file);

        // Convert PHP notices/warnings into exceptions to fail fast
        $prevHandler = set_error_handler(function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) { return false; }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        // Attempt to load the plugin main file
        require_once $this->plugin_file;

        // Let WordPress run its normal lifecycle bits that many plugins hook into
        do_action('plugins_loaded');
        do_action('init');

        // Basic sanity: site_url available, no output buffering leaks
        $this->assertNotEmpty( site_url(), 'WordPress not initialized as expected.' );

        // Restore error handler
        if ($prevHandler) { set_error_handler($prevHandler); }
        $this->assertTrue(true);
    }

    public function test_rest_routes_are_available(): void {
        // Ensure REST API is responsive and returns a routes array
        $server = rest_get_server();
        $this->assertNotNull($server, 'REST server not available.');
        $routes = $server->get_routes();
        $this->assertIsArray($routes, 'REST routes not an array.');
        $this->assertNotEmpty($routes, 'No REST routes registered at all.');
    }
}
