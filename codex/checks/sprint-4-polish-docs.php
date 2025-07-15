<?php
require_once dirname(__DIR__, 2) . '/wp-load.php';

$errors = [];

// Sprint 4: docs exist
$docs = [
    'docs/codex-sprint-1-map-qa.md',
    'docs/codex-sprint-2-donations-calendar.md',
    'docs/codex-sprint-3-ranking-api.md',
    'docs/codex-sprint-4-polish-docs.md'
];
foreach ( $docs as $doc ) {
    if ( ! file_exists( dirname(__DIR__, 2) . '/' . $doc ) ) {
        $errors[] = "$doc missing";
    }
}

if ( $errors ) {
    foreach ( $errors as $e ) {
        echo "[FAIL] $e\n";
    }
    exit(1);
}

echo "Sprint 4 checks passed\n";
