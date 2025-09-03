<?php
namespace {
    require_once __DIR__ . '/../TestStubs.php';
}

namespace ArtPulse\Frontend\Tests {

use PHPUnit\Framework\TestCase;
use ArtPulse\Tests\Stubs\MockStorage;

if ( ! function_exists( __NAMESPACE__ . '\\has_post_thumbnail' ) ) {
    function has_post_thumbnail(): bool {
        return false;
    }
}

if ( ! function_exists( __NAMESPACE__ . '\\wp_get_post_terms' ) ) {
    function wp_get_post_terms( $post_id, $taxonomy, $args = [] ) {
        return [];
    }
}

if ( ! function_exists( __NAMESPACE__ . '\\sanitize_email' ) ) {
    function sanitize_email( $email ) {
        return $email;
    }
}

if ( ! function_exists( __NAMESPACE__ . '\\antispambot' ) ) {
    function antispambot( $email ) {
        return str_replace( '@', '&#064;', $email );
    }
}

if ( ! function_exists( __NAMESPACE__ . '\\is_wp_error' ) ) {
    function is_wp_error( $thing ) {
        return false;
    }
}

/**
 * @group FRONTEND
 */
class EventTemplateTest extends TestCase {
    protected function setUp(): void {
        MockStorage::$post_meta = [];
    }

    public function test_organizer_email_obfuscated(): void {
        MockStorage::$post_meta = [
            '_ap_event_date' => '',
            '_ap_event_venue' => '',
            '_ap_event_address' => '',
            '_ap_event_start_time' => '',
            '_ap_event_end_time' => '',
            '_ap_event_contact' => '',
            '_ap_event_rsvp' => '',
            'event_organizer_email' => 'organizer@example.com',
        ];

        $html = $this->render_template(
            __DIR__ . '/../../templates/salient/content-artpulse_event.php'
        );

        $this->assertStringContainsString( '&#64;', $html );
        $this->assertStringNotContainsString( 'organizer@example.com', $html );
        $this->assertStringNotContainsString( '@', $html );
    }

    private function render_template( string $path ): string {
        $template = file_get_contents( $path );
        ob_start();
        eval( 'namespace ' . __NAMESPACE__ . '; ?>' . $template );
        return ob_get_clean();
    }
}

}
