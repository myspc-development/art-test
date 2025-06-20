<?php

namespace ArtPulse\Core;

use ArtPulse\Community\FavoritesManager;
use ArtPulse\Community\ProfileLinkRequestManager;
use ArtPulse\Community\FollowManager;
use ArtPulse\Community\NotificationManager;

/**
 * Handles installation of required database tables for ArtPulse.
 */
class Installer
{
    /**
     * Install all required DB tables for the plugin.
     */
    public static function install_all_tables()
    {
        if ( method_exists(FavoritesManager::class, 'install_favorites_table') ) {
            FavoritesManager::install_favorites_table();
        }

        if ( method_exists(ProfileLinkRequestManager::class, 'install_link_request_table') ) {
            ProfileLinkRequestManager::install_link_request_table();
        }

        if ( method_exists(FollowManager::class, 'install_follows_table') ) {
            FollowManager::install_follows_table();
        }

        if ( method_exists(NotificationManager::class, 'install_notifications_table') ) {
            NotificationManager::install_notifications_table();
        }

        if ( method_exists(RoleSetup::class, 'install') ) {
            RoleSetup::install();
        }
    }
}
