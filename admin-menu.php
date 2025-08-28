<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use ArtPulse\DashboardBuilder\DashboardManager;

// Register the new Dashboard Builder admin page.
DashboardManager::register();
