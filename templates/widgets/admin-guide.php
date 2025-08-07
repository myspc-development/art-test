<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
if (!user_can(get_current_user_id(), 'read')) return;
$args = $args ?? [];
$args['widget_id'] = 'admin_guide';
include __DIR__ . '/guide.php';

