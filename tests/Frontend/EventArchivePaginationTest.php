<?php
namespace {
    require_once __DIR__ . '/../TestStubs.php';

    if ( ! function_exists( 'post_type_archive_title' ) ) {
        function post_type_archive_title() { echo 'Events'; }
    }
    if ( ! function_exists( 'the_permalink' ) ) {
        function the_permalink() { echo '/event/test'; }
    }
    if ( ! function_exists( 'has_post_thumbnail' ) ) {
        function has_post_thumbnail() { return false; }
    }
    if ( ! function_exists( 'paginate_links' ) ) {
        function paginate_links( $args = array() ) {
            $params = $_GET;
            $max    = $GLOBALS['wp_query']->max_num_pages ?? 1;
            $links  = array();
            for ( $i = 1; $i <= $max; $i++ ) {
                $params['paged'] = $i;
                $links[]         = '<a href="?' . http_build_query( $params ) . '">' . $i . '</a>';
            }
            return implode( '', $links );
        }
    }
}

namespace ArtPulse\Frontend\Tests {
    use PHPUnit\Framework\TestCase;
    use ArtPulse\Tests\Stubs\MockStorage;

    /**
     * @group FRONTEND
     */
    class EventArchivePaginationTest extends TestCase {
        protected function setUp(): void {
            MockStorage::$have_posts = true;
            MockStorage::$post_meta  = array(
                '_ap_event_date'  => '',
                '_ap_event_venue' => '',
            );
        }

        public function test_pagination_links_include_query_vars(): void {
            set_query_var( 'ap_dashboard_role', 'artist' );
            set_query_var( 'type', 'music' );
            set_query_var( 'paged', 2 );

            $GLOBALS['wp_query']        = (object) array( 'max_num_pages' => 3 );
            $_SERVER['REQUEST_URI'] = '/events?' . http_build_query( $_GET );

            ob_start();
            include __DIR__ . '/../../templates/salient/archive-artpulse_event.php';
            $html = ob_get_clean();

            self::assertStringContainsString( 'ap_dashboard_role=artist', $html );
            self::assertStringContainsString( 'type=music', $html );
            self::assertStringContainsString( 'paged=1', $html );
            self::assertStringContainsString( 'paged=2', $html );
            self::assertStringContainsString( 'paged=3', $html );
        }
    }
}
