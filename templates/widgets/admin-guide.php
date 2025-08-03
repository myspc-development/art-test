<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
$args = $args ?? [];
$args['widget_id'] = 'admin_guide';
include __DIR__ . '/guide.php';

