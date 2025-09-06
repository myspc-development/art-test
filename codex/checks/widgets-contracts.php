<?php
$root = dirname(__DIR__, 2);
$errors = [];
$widgetFiles = glob($root . '/widgets/**/*.php');
foreach ($widgetFiles as $file) {
    $relative = substr($file, strlen($root . '/widgets/'));
    $base = substr($relative, 0, -4); // remove .php
    if ($base === 'placeholder-stubs') {
        continue;
    }
    $schema = $root . '/widgets/' . $base . '.schema.json';
    if (!file_exists($schema)) {
        $errors[] = 'Missing schema for widget ' . $base;
    }
    $tests = glob($root . '/tests/widgets/' . $base . '.test.*');
    $hasSnapshot = false;
    foreach ($tests as $test) {
        $contents = file_get_contents($test);
        if (strpos($contents, 'toMatchSnapshot') !== false) {
            $snap = dirname($test) . '/__snapshots__/' . basename($test) . '.snap';
            if (file_exists($snap)) {
                $hasSnapshot = true;
                break;
            }
        }
    }
    if (!$hasSnapshot) {
        $errors[] = 'Missing Jest snapshot test for widget ' . $base;
    }
}
if ($errors) {
    foreach ($errors as $e) {
        echo "[FAIL] $e\n";
    }
    exit(1);
}
echo "Widget contract check passed\n";
