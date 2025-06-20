<?php

namespace ArtPulse\Core\CLI;

use WP_CLI;
use ArtPulse\Core\Installer;

/**
 * WP-CLI command to manage ArtPulse DB installation.
 */
class DBInstallerCommand
{
    /**
     * Install all ArtPulse database tables.
     *
     * ## EXAMPLES
     *
     *     wp artpulse db install
     *
     * @when after_wp_load
     */
    public function install( $args = [], $assoc_args = [] )
    {
        WP_CLI::log('🔧 Installing ArtPulse DB tables...');

        try {
            Installer::install_all_tables();
            WP_CLI::success('✅ All ArtPulse DB tables installed successfully.');
        } catch ( \Throwable $e ) {
            WP_CLI::error('❌ Failed to install ArtPulse DB tables: ' . $e->getMessage());
        }
    }
}
