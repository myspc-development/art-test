#!/bin/sh
set -e
WP_PHPUNIT__TESTS_DIR=${WP_PHPUNIT__TESTS_DIR:-vendor/wp-phpunit/wp-phpunit}
WP_PHPUNIT__TESTS_CONFIG=${WP_PHPUNIT__TESTS_CONFIG:-tests/wp-tests-config.php}
export WP_PHPUNIT__TESTS_DIR WP_PHPUNIT__TESTS_CONFIG
vendor/bin/phpunit -c phpunit.wp.xml.dist --stop-on-failure tests/Integration/BootSmokeTest.php tests/Integration/DiagnosticsAjaxTest.php
