<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Frontend\ShortcodeRoleDashboard;

/**

 * @group integration

 */

class ShortcodeRoleDashboardTest extends \WP_UnitTestCase {
    public function set_up() : void {
        parent::set_up();
        ShortcodeRoleDashboard::register();
        add_filter( 'is_singular', '__return_true' );
    }

    public function test_shortcode_renders_for_post() : void {
        $user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
        wp_set_current_user( $user_id );
        $post_id = wp_insert_post( array(
            'post_title'   => 'Dashboard',
            'post_content' => '[ap_role_dashboard role="artist"]',
            'post_status'  => 'publish',
        ) );

        $this->go_to( get_permalink( $post_id ) );
        ShortcodeRoleDashboard::maybe_enqueue();
        $output = do_shortcode( get_post_field( 'post_content', $post_id ) );
        $this->assertStringContainsString( 'data-role="artist"', $output );
    }

    public function test_preview_injects_container() : void {
        $user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
        wp_set_current_user( $user_id );
        $post_id = wp_insert_post( array(
            'post_title'   => 'Preview',
            'post_content' => 'No shortcode here',
            'post_status'  => 'publish',
        ) );

        $nonce = wp_create_nonce( 'ap_preview' );
        $url   = get_permalink( $post_id ) . '?ap_preview_role=admin&ap_preview_nonce=' . $nonce;
        $this->go_to( $url );
        ShortcodeRoleDashboard::maybe_enqueue();
        $content = ShortcodeRoleDashboard::inject_container_if_preview( 'Original' );
        $this->assertStringContainsString( 'data-role="admin"', $content );
    }

    public function test_preview_overrides_shortcode_role() : void {
        $user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
        wp_set_current_user( $user_id );
        $post_id = wp_insert_post( array(
            'post_title'   => 'With Shortcode',
            'post_content' => '[ap_role_dashboard role="member"]',
            'post_status'  => 'publish',
        ) );

        $nonce = wp_create_nonce( 'ap_preview' );
        $url   = get_permalink( $post_id ) . '?ap_preview_role=organization&ap_preview_nonce=' . $nonce;
        $this->go_to( $url );
        ShortcodeRoleDashboard::maybe_enqueue();
        $output = do_shortcode( get_post_field( 'post_content', $post_id ) );
        $this->assertStringContainsString( 'data-role="organization"', $output );
    }
}
