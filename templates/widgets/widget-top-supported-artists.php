<section class="ap-top-supported-artists">
    <h3><?php esc_html_e('Top Supported Artists','artpulse'); ?></h3>
    <ul>
        <?php foreach ($artists as $artist_id => $amount): ?>
            <li><?php echo get_the_title($artist_id) . ' - $' . number_format($amount, 2); ?></li>
        <?php endforeach; ?>
    </ul>
</section>
