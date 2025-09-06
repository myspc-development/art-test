<?php
namespace ArtPulse\Tests;

class TimeMock {
	private static ?int $now        = null;
	private static string $timezone = 'UTC';

	public static function freeze( int $timestamp, string $tz = 'UTC' ): void {
		self::$now      = $timestamp;
		self::$timezone = $tz;
		add_filter( 'pre_option_timezone_string', array( __CLASS__, 'filter_timezone' ) );
		add_filter( 'current_time', array( __CLASS__, 'filter_current_time' ), 10, 3 );
	}

	public static function unfreeze(): void {
		self::$now = null;
		remove_filter( 'pre_option_timezone_string', array( __CLASS__, 'filter_timezone' ) );
		remove_filter( 'current_time', array( __CLASS__, 'filter_current_time' ), 10 );
	}

	public static function filter_timezone(): string {
		return self::$timezone;
	}

	public static function filter_current_time( $timestamp, $type, $gmt ) {
		return self::$now ?? $timestamp;
	}

	public static function now(): int {
		return self::$now ?? \time();
	}

	public static function wp_date( string $format, $timestamp = null, $timezone = null ): string {
		$timestamp = $timestamp ?? self::now();
		return \wp_date( $format, $timestamp, $timezone );
	}
}

namespace ArtPulse\Rest;

use ArtPulse\Tests\TimeMock;
function time(): int {
	return TimeMock::now(); }
function wp_date( string $format, $timestamp = null, $timezone = null ): string {
	return TimeMock::wp_date( $format, $timestamp, $timezone );
}

namespace ArtPulse;

use ArtPulse\Tests\TimeMock;
function time(): int {
	return TimeMock::now(); }
function wp_date( string $format, $timestamp = null, $timezone = null ): string {
	return TimeMock::wp_date( $format, $timestamp, $timezone );
}
