<?php
namespace ArtPulse\Personalization;

use ArtPulse\Traits\Registerable;

class WeeklyRecommendations
{
    use Registerable;

    private const HOOKS = [
        'init' => 'schedule_cron',
        'ap_generate_recommendations' => 'generate',
    ];

    public static function schedule_cron(): void
    {
        if (!wp_next_scheduled('ap_generate_recommendations')) {
            wp_schedule_event(time(), 'weekly', 'ap_generate_recommendations');
        }
    }

    public static function generate(): void
    {
        $users = get_users(['fields' => 'ids']);
        foreach ($users as $user_id) {
            $recs = RecommendationEngine::get_recommendations((int) $user_id, 'event', 5);
            $ids  = array_map(static fn($r) => (int) $r['id'], $recs);
            update_user_meta($user_id, 'ap_weekly_recommendations', $ids);
        }
    }
}
