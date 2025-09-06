<?php
namespace ArtPulse\Tests;

final class ErrorSilencer {
	public static function muteMissingWidgetWarning( int $errno, string $errstr ): bool {
		return $errno === E_USER_WARNING
			&& strpos( $errstr, 'Dashboard widget not registered' ) !== false;
	}
}
