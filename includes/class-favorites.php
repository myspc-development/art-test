<?php
if (!defined('ABSPATH')) { exit; }

use ArtPulse\Community\FavoritesManager;

/**
 * Simple wrapper around the FavoritesManager to provide
 * a public API for toggling and listing favorites.
 */
class ArtPulse_Favorites
{
    /**
     * Toggle a favorite for the current user.
     *
     * @param int    $object_id   The ID of the object to favorite.
     * @param string $object_type The object type (post type).
     * @return string "added" or "removed" depending on the new state.
     */
    public static function toggle_favorite(int $object_id, string $object_type): string
    {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return 'error';
        }

        if (FavoritesManager::is_favorited($user_id, $object_id, $object_type)) {
            FavoritesManager::remove_favorite($user_id, $object_id, $object_type);
            return 'removed';
        }

        FavoritesManager::add_favorite($user_id, $object_id, $object_type);
        return 'added';
    }

    /**
     * Retrieve favorites for a user.
     *
     * @param int         $user_id     User ID.
     * @param string|null $object_type Optional object type filter.
     * @return array List of favorite objects.
     */
    public static function get_user_favorites(int $user_id, string $object_type = null): array
    {
        return FavoritesManager::get_user_favorites($user_id, $object_type);
    }
}
