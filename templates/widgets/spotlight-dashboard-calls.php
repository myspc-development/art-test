<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
$args = $args ?? [];
$args['category']  = 'calls';
$args['widget_id'] = 'widget_spotlight_calls';
include __DIR__ . '/spotlight-dashboard.php';

