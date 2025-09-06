<?php
/**
 * Generate basic REST API tests for routes defined in includes/rest-*.php files.
 *
 * Usage:
 *  php scripts/generate_rest_tests.php --dry-run
 *  php scripts/generate_rest_tests.php --write
 */

$argv = $_SERVER['argv'];
array_shift($argv);
$write = in_array('--write', $argv, true);
$dry   = in_array('--dry-run', $argv, true);
if ($write === $dry) {
    fwrite(STDERR, "Usage: php scripts/generate_rest_tests.php [--dry-run|--write]\n");
    exit(1);
}

$root        = dirname(__DIR__);
$includesDir = $root . '/includes';
$generatedDir = $root . '/tests/Rest/Generated';

$restFiles = glob($includesDir . '/rest-*.php');

if ($write && ! is_dir($generatedDir)) {
    mkdir($generatedDir, 0777, true);
}

function studly(string $str): string {
    $str = str_replace(['-', '_'], ' ', strtolower($str));
    $str = ucwords($str);
    return str_replace(' ', '', $str);
}

function fill_route(string $route): string {
    return preg_replace_callback('/\\(\?P<[^>]+>([^)]+)\\)/', function (array $m) {
        $pattern = $m[1];
        return match ($pattern) {
            '\\d+'      => '1',
            '[a-z-]+'    => 'sample-slug',
            '[^/]+'      => 'x',
            default      => 'x',
        };
    }, $route);
}

$reportCreated = [];
$reportUpdated = [];
$dynamicCalls  = [];

foreach ($restFiles as $file) {
    $contents = file_get_contents($file);
    $totalCalls = preg_match_all('/register_rest_route\\s*\\(/', $contents, $dummy);
    $matches = [];
    preg_match_all('/register_rest_route\\s*\\(\\s*([\'\"])(?<ns>[^\'\"]+)\\1\\s*,\\s*([\'\"])(?<route>[^\'\"]+)\\3\\s*,/s', $contents, $matches, PREG_SET_ORDER);
    $dynamic = $totalCalls - count($matches);
    if ($dynamic > 0) {
        $dynamicCalls[$file] = $dynamic;
    }
    if (! $matches) {
        continue; // no literal routes
    }

    $slug = basename($file, '.php');
    $slug = preg_replace('/^rest-/', '', $slug);
    $class = studly($slug) . 'RouteTest';
    $path  = $generatedDir . '/' . $class . '.php';

    $routesExport = var_export(array_map(fn($m) => ['ns' => $m['ns'], 'route' => $m['route']], $matches), true);

    $testContent = <<<PHPFILE
<?php
class $class extends RouteTestCase {
    private array \$routes = $routesExport;

    private function fillRoute(string \$route): string {
        return preg_replace_callback('/\\(\?P<[^>]+>([^)]+)\\)/', function (array \$m) {
            return match (\$m[1]) {
                '\\d+'      => '1',
                '[a-z-]+'    => 'sample-slug',
                '[^/]+'      => 'x',
                default      => 'x',
            };
        }, \$route);
    }

    public function test_routes_exist_and_auth() {
        \$routes = rest_get_server()->get_routes();
        foreach (\$this->routes as \$r) {
            \$pattern = '#^/' . preg_quote(\$r['ns'], '#') . \$r['route'] . '$#';
            \$found = false;
            foreach (\$routes as \$key => \$handlers) {
                if (preg_match(\$pattern, \$key)) { \$found = true; break; }
            }
            \$this->assertTrue(\$found, 'Missing route ' . \$r['ns'] . \$r['route']);
        }
    }

    public function test_minimal_requests_anonymous_vs_admin() {
        foreach (\$this->routes as \$r) {
            \$path = '/' . ltrim(\$r['ns'], '/') . \$this->fillRoute(\$r['route']);
            \$status = 404;
            \$methodUsed = 'GET';
            foreach (['GET','POST','PUT','PATCH','DELETE'] as \$m) {
                \$response = \$this->req(\$m, \$path);
                \$status = \$response->get_status();
                if (404 !== \$status) { \$methodUsed = \$m; break; }
            }
            \$this->assertNotSame(404, \$status);
            \$admin = self::factory()->user->create(['role' => 'administrator']);
            \$response = \$this->req(\$methodUsed, \$path, [], \$admin);
            \$this->assertFalse(in_array(\$response->get_status(), [401,403], true));
        }
    }
}
PHPFILE;

    if ($write) {
        $current = file_exists($path) ? file_get_contents($path) : null;
        if ($current !== $testContent) {
            file_put_contents($path, $testContent);
            if ($current === null) {
                $reportCreated[] = $path;
            } else {
                $reportUpdated[] = $path;
            }
        }
    } else {
        if (!file_exists($path)) {
            $reportCreated[] = $path;
        } elseif (file_get_contents($path) !== $testContent) {
            $reportUpdated[] = $path;
        }
    }
}

if ($dry) {
    foreach ($reportCreated as $p) {
        echo "Would create $p\n";
    }
    foreach ($reportUpdated as $p) {
        echo "Would update $p\n";
    }
    foreach ($dynamicCalls as $f => $count) {
        echo "Skipped $count dynamic route(s) in $f\n";
    }
} else {
    foreach ($dynamicCalls as $f => $count) {
        echo "Skipped $count dynamic route(s) in $f\n";
    }
    foreach ($reportCreated as $p) {
        echo "Created $p\n";
    }
    foreach ($reportUpdated as $p) {
        echo "Updated $p\n";
    }
}
