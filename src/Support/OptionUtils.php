<?php
namespace ArtPulse\Support;

/**
 * Helper utilities for working with WordPress options.
 */
class OptionUtils {

	/**
	 * Retrieve an option as an array.
	 *
	 * Accepts JSON encoded strings or Traversable values and normalizes the
	 * result to an array. Non-array values return the provided default.
	 *
	 * @param string $name    Option name.
	 * @param array  $default Default value when the option is missing or invalid.
	 * @return array          Normalized option value.
	 */
	public static function get_array_option( string $name, array $default = array() ): array {
		$value = get_option( $name, $default );

		if ( is_array( $value ) ) {
			return $value;
		}

		if ( $value instanceof \Traversable ) {
			return iterator_to_array( $value );
		}

		if ( is_string( $value ) ) {
			$trim = trim( $value );
			if ( $trim !== '' && ( $trim[0] === '[' || $trim[0] === '{' ) ) {
				$decoded = json_decode( $trim, true );
				if ( is_array( $decoded ) ) {
					return $decoded;
				}
			}
		}

		return $default;
	}
}
