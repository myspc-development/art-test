<?php
namespace ArtPulse;

class DonationLogger
{
    public static function log(int $artist_id, string $name, float $amount): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_tips';
        $wpdb->insert($table, [
            'artist_id' => $artist_id,
            'user_id'   => get_current_user_id(),
            'amount'    => $amount,
            'tip_date'  => current_time('mysql'),
        ]);
        if ($name) {
            update_user_meta(get_current_user_id(), 'ap_supporter_name', sanitize_text_field($name));
        }
    }
}
