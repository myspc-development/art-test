<?php
/**
 * Template Name: Feature Spotlight
 */
get_header();
$spot_id = absint($_GET['spotlight'] ?? 0);
$post    = $spot_id ? get_post($spot_id) : null;
?>
<div class="ap-feature-checkout">
<?php if ($post && current_user_can('edit_post', $post->ID)): ?>
    <h1><?php echo esc_html($post->post_title); ?></h1>
    <form method="post">
        <?php wp_nonce_field('ap_feature_spotlight', 'ap_feature_nonce'); ?>
        <input type="hidden" name="spotlight_id" value="<?php echo esc_attr($post->ID); ?>">
        <button type="submit" class="ap-form-button nectar-button">
            <?php
            $opts  = get_option('artpulse_settings', []);
            $price = $opts['feature_price_spotlight'] ?? '5';
            printf( esc_html__( 'Feature for $%1$s', 'artpulse' ), esc_html( $price ) );
            ?>
        </button>
    </form>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ap_feature_nonce']) && wp_verify_nonce($_POST['ap_feature_nonce'], 'ap_feature_spotlight')) {
        $session = \ArtPulse\Payment\PaymentHandler::create_stripe_session(floatval($price), ['spotlight_id' => $post->ID]);
        if (!is_wp_error($session)) {
            wp_redirect($session->url);
            exit;
        }
        echo '<p>' . esc_html($session->get_error_message()) . '</p>';
    }
    ?>
<?php else: ?>
    <p><?php esc_html_e('Invalid spotlight.', 'artpulse'); ?></p>
<?php endif; ?>
</div>
<?php get_footer(); ?>
