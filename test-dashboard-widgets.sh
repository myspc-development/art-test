#!/bin/bash

echo "🔍 Running Dashboard Widgets System Checks..."

# CONFIGURATION
PLUGIN_SLUG="artpulse-management"
WIDGET_META_PREFIX="ap_dashboard_widgets_"
NONCE_ACTION="save_dashboard_widgets"
AJAX_URL="http://localhost/wp-admin/admin-ajax.php"
REST_URL="http://localhost/wp-json/artpulse/v1"
TEST_USER_ID=1
TEST_ROLE="subscriber"

# 1. LINT PHP FILES
echo "✅ Checking PHP syntax..."
find . -name "*.php" ! -path "./vendor/*" -exec php -l {} \;

# 2. RUN PHPCS (Requires squizlabs/php_codesniffer)
if command -v phpcs >/dev/null 2>&1; then
    echo "✅ Running PHPCS..."
    phpcs --standard=PSR12 --ignore=vendor . > phpcs.log || echo "⚠️ PHPCS issues found: check phpcs.log"
else
    echo "⚠️ PHPCS not installed. Skipping."
fi

# 3. TEST WIDGET DEFINITIONS
echo "✅ Checking registered widgets..."
wp eval '
use ArtPulse\Core\DashboardWidgetRegistry;
$defs = DashboardWidgetRegistry::get_definitions(true);
echo count($defs) . " widgets found\n";
foreach ($defs as $w) {
    echo "- " . $w["id"] . " (" . $w["name"] . ")\n";
}
'

# 4. CHECK WIDGET LAYOUT USER META
echo "✅ Checking saved widget layout for role '$TEST_ROLE'..."
wp user meta get $TEST_USER_ID "${WIDGET_META_PREFIX}${TEST_ROLE}" || echo "⚠️ No layout found."

# 5. CHECK WIDGET SETTINGS SCHEMA VIA REST
echo "✅ Testing REST schema for sample widget..."
SAMPLE_WIDGET=$(wp eval '
use ArtPulse\Core\DashboardWidgetRegistry;
$defs = DashboardWidgetRegistry::get_definitions();
echo $defs[0]["id"];
')
curl -s "${REST_URL}/widget-settings/${SAMPLE_WIDGET}" | jq

# 6. TEST WIDGET LAYOUT SAVE VIA AJAX (mock test)
echo "✅ Mock-saving layout via AJAX..."
curl -s -X POST "$AJAX_URL" \
  -d "action=ap_save_dashboard_widget_config" \
  -d "nonce=$(wp create nonce $NONCE_ACTION)" \
  -d "config[$TEST_ROLE][]=${SAMPLE_WIDGET}" | jq

# 7. OPTIONAL: RUN UNIT TESTS
if [ -f phpunit.xml.dist ]; then
    echo "✅ Running PHPUnit tests..."
    ./vendor/bin/phpunit || echo "⚠️ PHPUnit failed"
else
    echo "⚠️ No PHPUnit config found."
fi

echo "✅ Dashboard Widgets System Check Complete."
