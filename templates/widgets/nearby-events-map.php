<?php
extract(ap_template_context($args ?? [], ['visible' => true]));
$api_root = esc_url_raw(rest_url());
$nonce    = wp_create_nonce('wp_rest');
$lat      = get_user_meta(get_current_user_id(), 'ap_lat', true);
$lng      = get_user_meta(get_current_user_id(), 'ap_lng', true);
?>
<div id="nearby-events-map" class="ap-card" role="region" aria-labelledby="nearby-events-title" data-widget="nearby-events-map" <?php echo $visible ? '' : 'hidden'; ?>>
    <h2 id="nearby-events-title" class="ap-card__title"><?php esc_html_e('Nearby Events','artpulse'); ?></h2>
    <div class="ap-nearby-events-widget" data-api-root="<?php echo esc_attr($api_root); ?>" data-nonce="<?php echo esc_attr($nonce); ?>" data-lat="<?php echo esc_attr($lat); ?>" data-lng="<?php echo esc_attr($lng); ?>"></div>
</div>
