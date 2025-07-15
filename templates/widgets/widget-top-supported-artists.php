<div class="ap-card" role="region" aria-labelledby="ap-top-supported-title">
    <h2 id="ap-top-supported-title" class="ap-card__title"><?php esc_html_e('Top Supported Artists','artpulse'); ?></h2>
    <ul>
        <?php foreach ($artists as $artist_id => $amount): ?>
            <li><?php echo get_the_title($artist_id) . ' - $' . number_format($amount, 2); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
