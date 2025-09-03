<?php
namespace ArtPulse\Frontend\Html;

function extract_widget_ids(string $html): array {
    // Accept <div data-widget-id="..."> OR id="widget_..." OR data-ap-widget="..."
    $ids = [];

    // data-widget-id
    if (preg_match_all('/data-widget-id=["\']([^"\']+)["\']/i', $html, $m)) {
        $ids = array_merge($ids, $m[1]);
    }
    // data-ap-widget
    if (preg_match_all('/data-ap-widget=["\']([^"\']+)["\']/i', $html, $m2)) {
        $ids = array_merge($ids, $m2[1]);
    }
    // id="widget_xxx"
    if (preg_match_all('/id=["\'](widget_[a-z0-9_\-]+)["\']/i', $html, $m3)) {
        $ids = array_merge($ids, $m3[1]);
    }

    // canonicalize aliases we know about
    $aliases = ['widget_news_feed' => 'widget_news'];
    $seen = [];
    $out = [];
    foreach ($ids as $id) {
        $id = $aliases[$id] ?? $id;
        if (!isset($seen[$id])) { $seen[$id] = 1; $out[] = $id; }
    }
    return $out;
}
