<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Core\DashboardController;

class ResolvedRoleHeaderTest extends \WP_UnitTestCase {
    public function set_up() {
        parent::set_up();
        if ( ! defined( 'AP_VERBOSE_DEBUG' ) ) {
            define( 'AP_VERBOSE_DEBUG', true );
        }
    }

    public static function userProvider(): array {
        return [
            'admin'       => ['administrator', true],
            'subscriber'  => ['subscriber', true],
            'logged_out'  => [null, false],
        ];
    }

    /**
     * @dataProvider userProvider
     */
    public function test_header_emitted_only_for_logged_in_users( ?string $role, bool $expected ): void {
        header_remove();

        if ( $role ) {
            $uid = self::factory()->user->create( [ 'role' => $role ] );
            wp_set_current_user( $uid );
        } else {
            wp_set_current_user( 0 );
        }

        $_GET['role'] = 'member';

        $q = new \WP_Query();
        DashboardController::resolveRoleIntoQuery( $q );

        do_action( 'send_headers' );

        $headers = headers_list();
        $found   = false;

        foreach ( $headers as $header ) {
            if ( stripos( $header, 'X-AP-Resolved-Role: member' ) === 0 ) {
                $found = true;
                break;
            }
        }

        $this->assertSame( $expected, $found );
    }
}
