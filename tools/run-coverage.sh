#!/usr/bin/env bash
set -euo pipefail

mkdir -p build

if php -m | grep -q pcov; then
  php -d pcov.enabled=1 -d pcov.directory=src vendor/bin/phpunit -c phpunit.unit.xml.dist --coverage-clover build/coverage-unit.xml
  php -d pcov.enabled=1 -d pcov.directory=src vendor/bin/phpunit -c phpunit.wp.xml.dist --coverage-clover build/coverage-wp.xml
else
  XDEBUG_MODE=coverage vendor/bin/phpunit -c phpunit.unit.xml.dist --coverage-clover build/coverage-unit.xml
  XDEBUG_MODE=coverage vendor/bin/phpunit -c phpunit.wp.xml.dist --coverage-clover build/coverage-wp.xml
fi

php tools/coverage-check.php build/coverage-unit.xml build/coverage-wp.xml 70 \
  ArtPulse\\Core\\DashboardController=90 \
  ArtPulse\\Core\\DashboardWidgetRegistry=90 \
  ArtPulse\\Core\\DashboardPresets=90
