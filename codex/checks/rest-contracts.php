<?php
$root = dirname(__DIR__, 2);
$errors = [];
function studly(string $str): string {
    $str = str_replace(['-', '_'], ' ', strtolower($str));
    $str = ucwords($str);
    return str_replace(' ', '', $str);
}
// Check includes/rest-*.php files
foreach (glob($root . '/includes/rest-*.php') as $file) {
    $slug = basename($file, '.php');
    $slug = preg_replace('/^rest-/', '', $slug);
    $name = studly($slug);
    $candidates = [
        $root . '/tests/Rest/' . $name . 'Test.php',
        $root . '/tests/Rest/Rest' . $name . 'Test.php',
    ];
    $found = false;
    foreach ($candidates as $candidate) {
        if (file_exists($candidate)) {
            $found = true;
            break;
        }
    }
    if (!$found) {
        $errors[] = 'Missing REST test for includes/' . basename($file);
    }
}
// Check src/Rest/*.php controllers
foreach (glob($root . '/src/Rest/*.php') as $file) {
    $base = basename($file, '.php');
    if (str_contains($base, 'Test')) {
        continue;
    }
    $expected = $root . '/tests/Rest/' . $base . 'Test.php';
    if (!file_exists($expected)) {
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
