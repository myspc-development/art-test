<?php
namespace ArtPulse\Frontend;

if ( ! function_exists( __NAMESPACE__ . '\\get_post' ) ) {
	function get_post( $id = null ) {
		return null; }
}
if ( ! function_exists( __NAMESPACE__ . '\\get_user_meta' ) ) {
	function get_user_meta( $user_id, $key, $single = false ) {
		return ''; }
}
if ( ! function_exists( __NAMESPACE__ . '\\get_permalink' ) ) {
	function get_permalink( $post_id = 0 ) {
		return ''; }
}
if ( ! function_exists( __NAMESPACE__ . '\\current_user_can' ) ) {
	function current_user_can( $capability ) {
		return true; }
}
if ( ! function_exists( __NAMESPACE__ . '\\do_shortcode' ) ) {
	function do_shortcode( $content ) {
		return $content; }
}
