# Tests Audit Report

## Harness
- Integration suite configured via `phpunit.wp.xml.dist` and bootstraps through `tests/bootstrap.php`.
- Added `tests/wp-tests-config-sample.php`; copy to `tests/wp-tests-config.php` for local runs.
- Composer script `test:wp` now exports `WP_PHPUNIT__TESTS_DIR` and `WP_PHPUNIT__TESTS_CONFIG`.
- `tests/bootstrap.php` locates a WordPress source if `WP_PHPUNIT__WP_DIR` is missing, defines `WP_TESTS_CONFIG_FILE_PATH`, and adds an optional closure-serialization guard (enable with `AP_TEST_CLOSURE_GUARD`).

Run integration tests with:
```
WP_PHPUNIT__TESTS_DIR=vendor/wp-phpunit/wp-phpunit \
WP_PHPUNIT__TESTS_CONFIG=tests/wp-tests-config.php \
vendor/phpunit/phpunit/phpunit -c phpunit.wp.xml.dist
```

## Fixtures / Base Classes
Standardized `set_up()`/`tear_down()` methods and WordPress base classes in:
- `tests/Ajax/FrontendFilterHandlerTest.php`
- `tests/Admin/DiagnosticsPageTest.php`
- `tests/Admin/UpdatesTabTest.php`
- `tests/Frontend/EventSubmissionShortcodeTest.php`
- `tests/Integration/EventEditAjaxTest.php`
- `tests/Integration/SaveUserLayoutAjaxTest.php`
- `tests/Integration/DiagnosticsAjaxTest.php`
- `tests/Integration/FeedbackAjaxTest.php`
- `tests/Integration/ReleaseNotesAjaxTest.php`

## AJAX Tests
Nonce seeding and helper trait usage verified in the above AJAX tests; superglobals reset in `tear_down()`.

## Determinism
Outbound HTTP calls mocked via `pre_http_request` in:
- `tests/AI/OpenAIClientTest.php`
- `tests/Admin/UpdatesTabTest.php`

## DB Diagnostic
`tests/Integration/DbTablesExistTest.php` asserts presence of custom tables:
`ap_auctions`, `ap_bids`, `ap_donations`, `ap_event_tickets`, `ap_feedback`, `ap_feedback_comments`, `ap_messages`, `ap_org_messages`, `ap_org_user_roles`, `ap_payouts`, `ap_promotions`, `ap_roles`, `ap_rsvps`, `ap_scheduled_messages`, `ap_tickets`.

## Unit Suite
Closure serialization guard available via `AP_TEST_CLOSURE_GUARD` in `tests/bootstrap.php`.

## Smoke & Harness Tools
- Added `tests/Integration/BootSmokeTest.php`.
- Added `scripts/harness-check.sh` for quick setup validation.

## Next Steps
- Ensure `WP_PHPUNIT__WP_DIR` points to a valid WordPress installation before running tests.
- Consider CI coverage across PHP and WordPress versions.
