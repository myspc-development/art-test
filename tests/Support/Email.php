<?php
namespace ArtPulse\Tests;

/**
 * Utility for capturing emails sent during tests.
 */
final class Email {
    /** @var array<int, array<int, mixed>> */
    private static array $messages = array();
    private static bool $installed = false;

    /**
     * Install the mail capture filter.
     */
    public static function install(): void {
        if ( self::$installed ) {
            return;
        }
        self::$installed = true;
        add_filter( 'pre_wp_mail', array( self::class, 'capture' ), 10, 6 );
    }

    /**
     * Filter callback to record mail parameters.
     *
     * @return bool Always true to short-circuit wp_mail.
     */
    public static function capture( $null, $to, $subject, $message, $headers, $attachments ): bool {
        self::$messages[] = func_get_args();
        return true;
    }

    /**
     * Retrieve all captured messages.
     *
     * @return array<int, array<int, mixed>>
     */
    public static function messages(): array {
        return self::$messages;
    }

    /**
     * Clear captured messages.
     */
    public static function clear(): void {
        self::$messages = array();
    }
}
