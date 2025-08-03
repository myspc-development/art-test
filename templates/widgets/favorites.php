<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
$args = $args ?? [];
$args['widget_id'] = 'favorites';
$args['id'] = $args['id'] ?? 'favorites';
include __DIR__ . '/my-favorites.php';

