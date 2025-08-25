<?php
if (!defined('ABSPATH')) {
    exit;
}
if (function_exists('ap_dashboard_v2_enabled') && !ap_dashboard_v2_enabled()) {
    return;
}
?>
<nav class="ap-dashboard-nav" data-ap-nav>
    <ul id="ap-nav-list" role="tablist"></ul>
</nav>
