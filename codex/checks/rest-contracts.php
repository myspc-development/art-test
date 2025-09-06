<?php
$root = dirname(__DIR__, 2);
$errors = [];

function studly(string $str): string {
    $str = str_replace(['-', '_'], ' ', strtolower($str));
    $str = ucwords($str);
    return str_replace(' ', '', $str);
}

function is_utility_file(string $path): bool {
    $name = basename($path);
    if (preg_match('~rest-.*(util|utils)~i', $name)) {
        return true;
    }
    $code = @file_get_contents($path) ?: '';
    if (!preg_match('~register_rest_route\s*\(~', $code) &&
        !preg_match('~class\s+\w+.*\{.*function\s+(register_routes|register)\s*\(~s', $code)) {
        return true;
    }
    return false;
}

function map_rest_file_to_test_candidates(string $file): array {
    global $root, $map;
    $candidates = [];
    if (str_contains($file, '/includes/')) {
        $slug = basename($file, '.php');
        $slug = preg_replace('/^rest-/', '', $slug);
        $name = studly($slug);
        $candidates = [
            $root . '/tests/Rest/' . $name . 'Test.php',
            $root . '/tests/Rest/Rest' . $name . 'Test.php',
            $root . '/tests/Rest/Generated/' . $name . 'RouteTest.php',
        ];
        if (isset($map[$slug])) {
            $candidates[] = $root . '/tests/Rest/' . $map[$slug];
        }
    } else {
        $base = basename($file, '.php');
        $candidates = [
            $root . '/tests/Rest/' . $base . 'Test.php',
        ];
    }
    return $candidates;
}

// Check includes/rest-*.php files
$map = [ 'auth-code' => 'AuthCodeRouteTest.php' ];
$smoke = file_exists($root . '/tests/Rest/Generated/RestRoutesSmokeTest.php');
foreach (glob($root . '/includes/rest-*.php') as $file) {
    if (is_utility_file($file)) {
        continue;
    }
    $candidates = map_rest_file_to_test_candidates($file);
    $ok = $smoke;
    foreach ($candidates as $candidate) {
        if (file_exists($candidate)) {
            $ok = true;
            break;
        }
    }
    if (!$ok) {
        $errors[] = 'Missing REST test for includes/' . basename($file);
    }
}

// Check src/Rest/*.php controllers
foreach (glob($root . '/src/Rest/*.php') as $file) {
    $base = basename($file, '.php');
    if (str_contains($base, 'Test')) {
        continue;
    }
    if (is_utility_file($file)) {
        continue;
    }
    $candidates = map_rest_file_to_test_candidates($file);
    $ok = $smoke;
    foreach ($candidates as $candidate) {
        if (file_exists($candidate)) {
            $ok = true;
            break;
        }
    }
    if (!$ok) {
        $errors[] = 'Missing REST test for src/Rest/' . $base . '.php';
    }
}

if ($errors) {
    foreach ($errors as $e) {
        echo "[FAIL] $e\n";
    }
    exit(1);
}

echo "REST route contract check passed\n";
