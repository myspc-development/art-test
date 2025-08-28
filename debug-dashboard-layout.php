<?php
// debug-dashboard-layout.php

use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\WidgetVisibilityManager;

$user_id = get_current_user_id(); // Or set manually
$role    = DashboardController::get_role( $user_id );
$layout  = DashboardController::get_user_dashboard_layout( $user_id );

echo "<h2>🧩 Debug Dashboard for User ID: $user_id (Role: $role)</h2>";

if ( empty( $layout ) ) {
	echo "<p style='color:red;'>❌ No layout found for this user.</p>";
} else {
	echo '<p>✅ Layout loaded with ' . count( $layout ) . ' widgets.</p>';
}

echo '<h3>Layout Dump:</h3><pre>';
print_r( $layout );
echo '</pre>';

echo '<h3>Checking each widget:</h3>';

foreach ( $layout as $item ) {
	$id      = $item['id'] ?? '(missing)';
	$visible = $item['visible'] ?? true;

	echo "<div style='margin-bottom:1em;'>";
	echo "<strong>🧱 Widget ID:</strong> $id<br>";

	$widget = DashboardWidgetRegistry::get_widget( $id, $user_id );

	if ( ! $widget ) {
		echo "<span style='color:red;'>❌ Not registered in DashboardWidgetRegistry</span><br>";
	} else {
		echo "<span style='color:green;'>✅ Registered</span><br>";
		echo 'Title: ' . esc_html( $widget['title'] ?? '(no title)' ) . '<br>';
	}

	if ( ! $visible ) {
		echo "<span style='color:orange;'>⚠️ Widget marked as not visible</span><br>";
	}

	// Visibility logic (role/capability check)
	$visible_rules = WidgetVisibilityManager::get_visibility_rules( $id );
	echo 'Visibility Rules: <pre>' . print_r( $visible_rules, true ) . '</pre>';

	echo '</div>';
}

echo '<h3>Registered Widgets (Total: ' . count( DashboardWidgetRegistry::get_all() ) . ')</h3><pre>';
print_r( DashboardWidgetRegistry::get_all() );
echo '</pre>';
