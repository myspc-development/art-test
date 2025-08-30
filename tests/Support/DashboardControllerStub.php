<?php
declare(strict_types=1);

namespace ArtPulse\Tests\Stubs;

/**
 * Test stub for the production DashboardController.
 * Provides a deterministic role resolver for unit tests (no WP required).
 *
 * Mapped to \ArtPulse\Core\DashboardController via class_alias from the
 * unit bootstrap so production code transparently uses this during unit tests.
 */
final class DashboardControllerStub
{
    /** @var string */
    private static string $defaultRole = 'member';

    /**
     * Allow tests to override the default role used when we cannot derive one.
     */
    public static function set_default_role(string $role): void
    {
        self::$defaultRole = self::sanitize($role);
    }

    /**
     * Production code expects: DashboardController::get_role(int $user_id): string
     */
    public static function get_role(int $user_id = 0): string
    {
        // If WP is loaded (integration tests), prefer actual user roles.
        if (\function_exists('get_userdata')) {
            $uid = $user_id;
            if (!$uid && \function_exists('get_current_user_id')) {
                $uid = (int) \get_current_user_id();
            }
            if ($uid > 0) {
                $u = \get_userdata($uid);
                if ($u && !empty($u->roles) && \is_array($u->roles)) {
                    $first = (string) \reset($u->roles);
                    if ($first !== '') {
                        return self::sanitize($first);
                    }
                }
            }
        }

        // Honor an env override for deterministic unit tests.
        $forced = \getenv('AP_TEST_ROLE');
        if (\is_string($forced) && $forced !== '') {
            return self::sanitize($forced);
        }

        return self::$defaultRole;
    }

    /**
     * Minimal sanitize_key fallback (don’t assume WP is loaded in unit tests).
     */
    private static function sanitize(string $value): string
    {
        $value = \strtolower($value);
        // keep a-z, 0-9, _, -
        return (string) \preg_replace('/[^a-z0-9_\-]/', '', $value);
    }
}

// In unit tests, production class may not exist. Alias the stub so
// \ArtPulse\Core\DashboardController::get_role() resolves to this implementation.
if (!\class_exists(\ArtPulse\Core\DashboardController::class)) {
    \class_alias(
        \ArtPulse\Tests\Stubs\DashboardControllerStub::class,
        \ArtPulse\Core\DashboardController::class
    );
}
