<?php
/**
 * Template Name: Feature Event
 */
get_header();
$event_id = absint($_GET['event'] ?? 0);
$event    = $event_id ? get_post($event_id) : null;
?>
<div class="ap-feature-checkout">
<?php if ($event && current_user_can('edit_post', $event->ID)): ?>
    <h1><?php echo esc_html($event->post_title); ?></h1>
    <form method="post">
        <?php wp_nonce_field('ap_feature_event', 'ap_feature_nonce'); ?>
        <input type="hidden" name="event_id" value="<?php echo esc_attr($event->ID); ?>">
        <button type="submit" class="ap-form-button nectar-button">
            <?php
            $opts  = get_option('artpulse_settings', []);
            $price = $opts['feature_price_event'] ?? '10';
            printf( esc_html__( 'Feature for $%1$s', 'artpulse' ), esc_html( $price ) );
            ?>
        </button>
    </form>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ap_feature_nonce']) && wp_verify_nonce($_POST['ap_feature_nonce'], 'ap_feature_event')) {
        $session = \ArtPulse\Payment\PaymentHandler::create_stripe_session(floatval($price), ['event_id' => $event->ID]);
        if (!is_wp_error($session)) {
            wp_redirect($session->url);
            exit;
        }
        echo '<p>' . esc_html($session->get_error_message()) . '</p>';
    }
    ?>
<?php else: ?>
    <p><?php esc_html_e('Invalid event.', 'artpulse'); ?></p>
<?php endif; ?>
</div>
<?php get_footer(); ?>
