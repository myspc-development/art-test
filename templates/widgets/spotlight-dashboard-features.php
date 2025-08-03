<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
$args = $args ?? [];
$args['category']  = 'featured';
$args['widget_id'] = 'widget_spotlight_features';
include __DIR__ . '/spotlight-dashboard.php';

