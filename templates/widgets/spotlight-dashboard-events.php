<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
$args = $args ?? [];
$args['category']  = 'events';
$args['widget_id'] = 'widget_spotlight_events';
include __DIR__ . '/spotlight-dashboard.php';

