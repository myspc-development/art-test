<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
extract(ap_template_context($args ?? [], ['visible' => true]));
$api_root = esc_url_raw(rest_url());
$nonce    = wp_create_nonce('wp_rest');
?>
<div id="my-favorites" class="ap-card" role="region" aria-labelledby="my-favorites-title" data-widget="widget_favorites" <?php echo $visible ? '' : 'hidden'; ?>>
    <h2 id="my-favorites-title" class="ap-card__title"><?php esc_html_e('My Favorite Events','artpulse'); ?></h2>
    <div class="ap-favorites-widget" data-api-root="<?php echo esc_attr($api_root); ?>" data-nonce="<?php echo esc_attr($nonce); ?>"></div>
</div>
