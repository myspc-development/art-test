<?php
use ArtPulse\Core\DashboardController;

$user_role = DashboardController::get_role(get_current_user_id());
include plugin_dir_path(__FILE__) . 'partials/dashboard-generic.php';

