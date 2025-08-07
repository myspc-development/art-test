<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
if (!user_can(get_current_user_id(), 'read')) return;
$args = $args ?? [];
$args['widget_id'] = 'favorites';
$args['id'] = $args['id'] ?? 'favorites';
include __DIR__ . '/my-favorites.php';

