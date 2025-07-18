<?php
namespace ArtPulse\Community;

use ArtPulse\Community\NotificationManager;

class CommunityShortcodeManager {
    public static function register() {
        add_shortcode('ap_notifications', [self::class, 'render']);
    }

    public static function render($atts = []) {
        if (!is_user_logged_in()) return '<p>Please log in to view your notifications.</p>';

        $user_id = get_current_user_id();
        $notifications = NotificationManager::get($user_id, 50);

        ob_start();
        ?>
        <div id="ap-notifications-widget">
            <h3>Your Notifications</h3>
            <button id="ap-refresh-notifications">🔄 Refresh</button>
            <ul id="ap-notification-list" role="status" aria-live="polite">
                <?php if (empty($notifications)): ?>
                    <li>No notifications.</li>
                <?php else: ?>
                    <?php foreach ($notifications as $notif): ?>
                        <li data-id="<?= esc_attr($notif->id) ?>">
                            <span><?= esc_html($notif->content ?: $notif->type) ?></span>
                            <?php if ($notif->status !== 'read'): ?>
                                <button class="mark-read">Mark as read</button>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
        <?php
        return ob_get_clean();
    }
}
