<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
if (!user_can(get_current_user_id(), 'read')) return;
$args = $args ?? [];
$args['category']  = 'events';
$args['widget_id'] = 'widget_spotlight_events';
include __DIR__ . '/spotlight-dashboard.php';

